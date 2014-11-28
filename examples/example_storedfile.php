<?php

/*
 * Example for usage with stored files
 */

require('../vendor/autoload.php');

use \Icontact\BooXtreamClient\BooXtreamClient;
use \GuzzleHttp\Client;

// Your username, apikey and BooXtream base url
$username = 'username';
$apikey = 'apikey';

// The storedfile you wish to use, with or without .epub
$storedfile = 'filename.epub';

// set the options in an array
$options = [
    'referenceid' => '1234567890',
    'customername' => 'customer',
    'customeremailaddress' => 'customer@example.com',
    'languagecode' => 1033, // 1033 = English
    'downloadlimit' => 3,
    'expirydays' => 30
];

// create a guzzle client with a base_url for the BooXtream service
$Guzzle = new Client();

// create the BooXtream Client
$BooXtream = new BooXtreamClient($Guzzle, $username, $apikey);

// create a request (could also be epub or mobi)
$BooXtream->createRequest('xml');

// set the stored file
$BooXtream->setStoredFile($storedfile);

// set the options
$BooXtream->setOptions($options);

// and send
$Response = $BooXtream->send();

// returns an array containing the response
var_dump($Response);
