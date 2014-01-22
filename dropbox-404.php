<?php
/*
Plugin Name: Dropbox 404
Plugin URI: http://github.com/10up/dropbox-404
Description: Search Dropbox Public folder and various redirects for filenames before returning 404.
Version: 1.0
Author: Paul Clark
Author URI: http://pdclark.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

add_action('template_redirect', create_function('', 'global $storm_dropbox_404; $storm_dropbox_404 = new Storm_Dropbox_404();') );

/**
 * 404 Error Document 
 * Searches defined aliases, local directories, and Dropbox accounts
 * Redirects or sends the file if found, displays 404 if not
 *
 * @author pdclark <pdclark.com>
 **/
class Storm_Dropbox_404 {

	/**
	 * Define keywords you want reserved for specific redirects or methods here
	 * @var array
	 */
	var $aliases;

	/**
	 * Directories on this web server to search for file matches
	 * @var array
	 */
	var $local_directories;

	/**
	 * Dropbox account and directories on them to search for file matches
	 * @var array
	 */
	var $dropbox_account;
	
	function __construct() {
		$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
		if ( !is_404() || empty( $path )) { return; }

		// Load configuration file
		$config_path = apply_filters('dropbox_404_config_path', WP_CONTENT_DIR.'/dropbox-404.ini.php' );
		if ( !file_exists($config_path) ) {
			return false;
		}
		$config = parse_ini_file( $config_path, true );

		if ( isset($config['aliases']) )
			$this->aliases = $config['aliases'];
		if ( isset($config['aliases']['local_directories']) )
			$this->local_directories = $config['aliases']['local_directories'];
		if ( isset($config['dropbox']) )
			$this->dropbox_account = $config['dropbox'];

		// Search for file
		$this->search_aliases( $path );
		$this->search_local_directories( $path );
		$this->search_dropbox( $path );

	}

	function search_aliases( $path ) {
		global $wp_query;
		
		$args = explode( '/', substr( $_SERVER['REQUEST_URI'], 1 ) );
		$keyword = array_shift($args); // First argument in URL names method in this class
		
		if ( array_key_exists( $keyword, $this->aliases ) ) {
			
			// There's an entry in $this->aliases
			$method = $this->aliases[ $keyword ];
			
			if ( method_exists( $this, $method ) ) {
				// Entry names a method

				$path = '/'.implode('/', $args);
				$url = $this->$method( $path );

				if ( $url ) { 
					// Method returned a URL or already sent file and exited
					status_header( 200 );
					$wp_query->is_404=false;
					header("Location: $url", true, 301); // Permanent Redirect
					exit;
				}
				
			}else {
				// Entry names a URL
				status_header( 200 );
				$wp_query->is_404=false;
				header( 'Location: '.$this->aliases[ $keyword ] , true, 301); // Permanent Redirect
				exit;
			}
			
		}
		
		return false;
	}
	
	function search_local_directories( $url ) {
		global $wp_query;

		if ( empty( $this->local_directories ) ) {
			return false;
		}

		foreach ($this->local_directories as $directory) {

			$path = $directory.parse_url( $url, PHP_URL_PATH );
			$full_path = $_SERVER["DOCUMENT_ROOT"].$path;
			
			if ( file_exists($full_path) && $path != $directory ) {
				status_header( 200 );
				$wp_query->is_404=false;
				header( 'Location: http://' . $_SERVER['SERVER_NAME'] . $path );
				exit;
				
			}

		}
		
		return false;
	}
	
	function search_dropbox($file) {
		global $wp_query;
		
		include 'dropbox/autoload.php';


		unset( $username, $password, $consumer_key, $consumer_secret, $directories, $token, $token_secret );
		extract( $this->dropbox_account ); // Sets the variables above
		
		// Init
		session_start();
		$oauth = new Dropbox_OAuth_PEAR( $consumer_key, $consumer_secret );
		$dropbox = new Dropbox_API( $oauth );

		if ( !empty( $token ) && !empty($token_secret) ) {
			$tokens = compact( 'token', 'token_secret' );
		}else if ( !empty($username) && !empty($password) ) {
			// Note that it's wise to save these tokens for re-use.
			$tokens = $dropbox->getToken( $username, $password ); 
		}

		$oauth->setToken($tokens);
		
		// Search directories
		foreach( $directories as $basedir ) {

			// Sanatize spaces & punctuation
			$path = $basedir . str_replace("%2F", "/", rawurlencode( $file ) ); 

			// Connect
			try {
				
				$meta = $dropbox->getMetaData( $path );
				if($meta['is_dir']) throw new Exception('Cannot download directory');

				status_header( 200 );
				$wp_query->is_404=false;

				header( 'Content-Type: '.$meta['mime_type'] );
				echo $dropbox->getFile( $path );
				exit;

			}catch(Exception $e) {
				return false;
			}

		}

	}

}


	


