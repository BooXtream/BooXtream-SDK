<?php

/*
 * Example for usage with stored files
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

// some required options
$referenceid = '1234567890';
$customername = 'customer';
$customeremailaddress = 'customer@example.com';
$languagecode = 1033; // English

// The storedfile you wish to use, with or without .epub
$storedfile = 'filename';

// create a guzzle client with a base_url for the BooXtream service
$base_url = 'https://service.booxtream.com';
$guzzle = new Client(['base_url' => $base_url]);

$BooXtream = new BooXtreamClient($guzzle, $username, $apikey);

// could also be 'epub' or 'mobi'
$BooXtream->createRequest('xml');
$BooXtream->setStoredFile($storedfile);

$options = [
    'referenceid' => $referenceid,
    'customername' => $customername,
    'customeremailaddress' => $customeremailaddress,
    'languagecode' => $languagecode
];
$BooXtream->setOptions($options);
$response = $BooXtream->send();

// returns an array containing the response
var_dump($response);
