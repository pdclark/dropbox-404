# Dropbox 404 #

Searches Dropbox accounts, local directories, defined aliases for a match to the requested file.
Redirects or sends the file if found, displays 404 if not.

Use by putting the dropbox-404 directory in your site's root directory and this line in *.htaccess* in your root directory:

    ErrorDocument 404 /dropbox-404/index.php

# Example Settings #

Edit in index.php:

    // Define keywords you want reserved for specific redirects or methods here
    var $aliases = array(
    	
    	// Reserved keywords redirect to URL.
    	'basecamp' 	=>	'http://domain.basecamphq.com',
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