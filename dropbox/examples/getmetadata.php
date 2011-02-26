<?php

/* Please supply your own consumer key and consumer secret */
$consumerKey = '8yj1j4kr5k6yo9v';
$consumerSecret = 'c5jwknvkqrnwr04';

include '../autoload.php';

session_start();

// $oauth = new Dropbox_OAuth_PHP($consumerKey, $consumerSecret);

// If the PHP OAuth extension is not available, you can try
// PEAR's HTTP_OAUTH instead.
$oauth = new Dropbox_OAuth_PEAR($consumerKey, $consumerSecret);

$dropbox = new Dropbox_API($oauth);

// header('Content-Type: text/plain');

$tokens = $dropbox->getToken('dropbox@pdclark.com', '6KJ869338YT'); 

// echo "Tokens:\n";
// print_r($tokens);

// Note that it's wise to save these tokens for re-use.
$oauth->setToken($tokens);



try {
	$meta = $dropbox->getMetaData('/Public/gps-points.jpg');
	if($meta['is_dir']) throw new Exception('Cannot download directory');

	echo '<pre>';
	print_r($meta);
	
	exit();
	header( 'Content-Type: '.$meta['mime_type'] );
	echo $dropbox->getFile( $meta['path'] );
}catch(Exception $e) {
	exit('404');
}




