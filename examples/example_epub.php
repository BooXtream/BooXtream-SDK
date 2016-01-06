<?php

/*
 * Example for usage with direct return of watermarked file
 */

require( '../vendor/autoload.php' );

use GuzzleHttp\Client;
use Icontact\BooXtreamClient\BooXtreamClient;

// Your username, apikey and BooXtream base url
$username = 'username';
$apikey   = 'apikey';

// The epubfile you would like to upload
$epubfile = 'assets/test.epub';

// set the options in an array
$options = [
	'referenceid'          => '1234567890',
	'customername'         => 'customer',
	'customeremailaddress' => 'customer@example.com',
	'languagecode'         => 1033 // 1033 = English
];

try {
	// create a guzzle client with a base_url for the BooXtream service
	$Guzzle = new Client();

	// create the BooXtream Client
	$BooXtream = new BooXtreamClient( $Guzzle, $username, $apikey );

	// create a request
	$BooXtream->createRequest( 'epub' );

	// set the epubfile
	$BooXtream->setEpubFile( $epubfile );

	// set the options
	$BooXtream->setOptions( $options );

	// and send
	$Response = $BooXtream->send();

	// returns a Response object, containing a returned epub
	var_dump( $Response );

} catch ( Exception $e ) {
	var_dump( $e );
}
