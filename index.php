<?php

$dropbox_404 = new dropbox_404();

/**
 * 404 Error Document 
 * Searches defined aliases, local directories, and Dropbox accounts
 * Redirects or sends the file if found, displays 404 if not.
 *
 * @author pdclark <pdclark.com>
 **/
class dropbox_404 {

	// Define keywords you want reserved for specific redirects or methods here
	var $aliases = array(
		
		// Reserved keywords redirect to URL.
		'basecamp' 	=>	'http://account.basecamphq.com',
		'git' 		=>	'https://github.com/account',
		'mail'		=>	'http://mail.google.com/a/domain.com',
		'docs'		=>	'http://docs.google.com/a/domain.com',
		'calendar'	=>	'http://calendar.google.com/a/domain.com',
		
		// Reserved keywords run method
		'files'		=>	'search_dropbox',
		'file'		=>	'search_dropbox',
		'f'			=>	'search_dropbox',
		
	);
	
	// Directories on this web server to search for file matches
	var $local_directories = array(
		'/sites',
		// '/files',
	);
	
	// Dropbox accounts and directories on them to search for file matches
	var $dropbox_accounts = array(
		array(
			'username'         => 'example@site.com', // E-mail address you log into dropbox.com with
			'password'         => '', // Password you log into dropbox.com with
			'consumer_key'     => '', // Developer key. See https://www.dropbox.com/developers/quickstart
			'consumer_secret'  => '', // Developer key. See https://www.dropbox.com/developers/quickstart
			'directories'      => array(
				'/Non-Public/Folder',
				'/Public',
			),
		),
		// Duplicate array to search additional accounts
		/*
		array(
			'username'         => 'example@site.com', // E-mail address you log into dropbox.com with
			'password'         => '', // Password you log into dropbox.com with
			'consumer_key'     => '', // Developer key. See https://www.dropbox.com/developers/quickstart
			'consumer_secret'  => '', // Developer key. See https://www.dropbox.com/developers/quickstart
			'directories'      => array(
				'/Non-Public/Folder',
				'/Public',
			),
		),
		*/
	);
	
	function not_found(){
		header( $_SERVER["SERVER_PROTOCOL"].' 404 Not Found');
		
		// Origiginal: http://lumino.us/404
		?>
		
		<h2>File Not Found</h2>

		<p>What has happened here?<br>
		Either we moved that webpage<br>
		Or someone mistyped</p>

		<p>Oh well, please try search<br>
		Or retyping, or something<br>
		Stronger&hellip; like bourbon</p>

		<a href="http://<?php echo $_SERVER['SERVER_NAME'] ?>/">Go Home</a>
		
		<?php
		
		exit;
	}
	
	// Okay, stop editing!
	function __construct() {
		
		$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
		if ( empty( $path )) { return; }
		
		if ( 
			!$this->search_aliases( $path )
			&& !$this->search_local_directories( $path )
			&& !$this->search_dropbox( $path )
		){
			$this->not_found();
		}
		
	}
	
	function search_aliases( $path ) {
		
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
					header("Location: $url", true, 301); // Permanent Redirect
					exit;
				}else {
					// Method found nothing
					$this->not_found();
				}
				
			}else {
				// Entry names a URL
				header( 'Location: '.$this->aliases[ $keyword ] , true, 301); // Permanent Redirect
				exit;
			}
			
		}
		
		
		
		return false;
	}
	
	function search_local_directories( $url ) {
		
		foreach ($this->local_directories as $directory) {

			$path = $directory.parse_url( $url, PHP_URL_PATH );
			$full_path = $_SERVER["DOCUMENT_ROOT"].$path;
			
			if ( file_exists($full_path) && $path != $directory ) {

				header( 'Location: http://' . $_SERVER['SERVER_NAME'] . $path );
				exit;
				
			}

		}
		
		return false;
	}
	
	function search_dropbox($file) {
		
		include 'dropbox/autoload.php';

		foreach( $this->dropbox_accounts as $account ) {

			unset( $username, $password, $consumer_key, $consumer_secret, $directories );
			extract( $account ); // Sets the variables above
			
			// Init
			session_start();
			$oauth = new Dropbox_OAuth_PEAR( $consumer_key, $consumer_secret );
			$dropbox = new Dropbox_API( $oauth );
			$tokens = $dropbox->getToken( $username, $password ); 
			// Note that it's wise to save these tokens for re-use.
			$oauth->setToken($tokens);
			
			// Search directories
			foreach( $directories as $basedir ) {

				// Sanatize spaces & punctuation
				$path = $basedir . str_replace("%2F", "/", rawurlencode( $file ) ); 

				// Connect
				try {
					
					$meta = $dropbox->getMetaData( $path );
					if($meta['is_dir']) throw new Exception('Cannot download directory');

					header( 'Content-Type: '.$meta['mime_type'] );
					echo $dropbox->getFile( $path );
					exit;

				}catch(Exception $e) {
					return false;
				}

			}

		}
		
	}

}


	


