<?php

/*
 * Example for usage with stored files
 */

require '../vendor/autoload.php';

use GuzzleHttp\Client;
use Icontact\BooXtreamClient\BooXtreamClient;
use Icontact\BooXtreamClient\Options;

// Your username, apikey and BooXtream base url
$credentials = ['username', 'apikey'];

// The storedfile you wish to use, with or without .epub
$storedfile         = '9789491833212_preview_edition.epub';
$storedexlibrisfile = 'exlibris-sample-logo.png';

// set the options in an array
$options = [
    'referenceid'          => '1234567890',
    'customername'         => 'customer',
    'customeremailaddress' => 'customer@example.com',
    'languagecode'         => 1033, // 1033 = English
    'downloadlimit'        => 3,
    'expirydays'           => 30,
];

// the type of request, in this case it's a request for a downloadlink embedded in xml
$type = 'xml';

try {
    // create a guzzle client
    $Guzzle = new Client();

    // create an options object
    $Options = new Options($options);

    // create the BooXtream Client
    $BooXtream = new BooXtreamClient($type, $Options, $credentials, $Guzzle);

    // set the stored file
    $BooXtream->setStoredEpubFile($storedfile);

    // It's also possible to set a stored exlibris file
    $BooXtream->setStoredExlibrisFile($storedexlibrisfile);

    // and send
    $Response = $BooXtream->send();

    // returns a Response object, containing returned xml
    var_dump($Response->getBody()->getContents());
} catch (Exception $e) {
    var_dump($e);
}
