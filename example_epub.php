<?php

/*
 * Example for usage with direct return of watermarked file
 *
 * BooXtreamClient can return:
 * - a succesful response containing the file in $response['raw'] and the content-type in $response['content-type']
 * - an error response ($response['Response']['Error'])
 *
 * At the moment it can also throw Exceptions if conditions are not met
 */

require('vendor/autoload.php');

use \Icontact\BooXtreamClient\BooXtreamClient;
use \Icontact\BooXtreamClient\EpubFile;
use \GuzzleHttp\Client;

// Your username and apikey
$username = 'username';
$apikey = 'apikey';

// The epubfile you would like to upload
$epubfile = '/location/to/file.epub';

// set the options in an array
$options = [
    'referenceid' => '1234567890',
    'customername' => 'customer',
    'customeremailaddress' => 'customer@example.com',
    'languagecode' => 1033 // 1033 = English
];

// create a guzzle client with a base_url for the BooXtream service
$Guzzle = new Client(['base_url' => $base_url]);

// create an epubfile for upload
$EpubFile = new EpubFile('epubfile', fopen($epubfile, 'r'));

// create the BooXtream Client
$BooXtream = new BooXtreamClient($Guzzle, $username, $apikey);

// create a request with the epubfile
$BooXtream->createRequest('epub', $EpubFile);

// set the options
$BooXtream->setOptions($options);

// and send
$response = $BooXtream->send();

// returns an array containing the response
var_dump($response);
