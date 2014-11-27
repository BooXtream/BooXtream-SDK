<?php

/*
 * Example for usage with distribution platform (xml with downloadlinks)
 *
 * BooXtreamClient can return:
 * - a succesful response
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

// some required options
$base_url = 'https://service.booxtream.com';
$referenceid = '1234567890';
$customername = 'customer';
$customeremailaddress = 'customer@example.com';
$languagecode = 1033; // English

// set the options in an array
$options = [
    'referenceid' => $referenceid,
    'customername' => $customername,
    'customeremailaddress' => $customeremailaddress,
    'languagecode' => $languagecode
];

// create a guzzle client with a base_url for the BooXtream service
$Guzzle = new Client(['base_url' => $base_url]);

// create an epubfile for upload
$EpubFile = new EpubFile('epubfile', fopen($epubfile, 'r'));

// create the BooXtream Client
$BooXtream = new BooXtreamClient($Guzzle, $username, $apikey);

// create a request with the epubfile
$BooXtream->createRequest('xml', $EpubFile);

// set the options
$BooXtream->setOptions($options);

// and send
$response = $BooXtream->send();

// returns an array containing the response
var_dump($response);
