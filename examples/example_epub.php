<?php

/*
 * Example for usage with direct return of watermarked file
 */

require __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;
use Icontact\BooXtreamClient\BooXtreamClient;
use Icontact\BooXtreamClient\Options;

// Your username, apikey and BooXtream base url
$credentials = ['username', 'apikey'];

// The epubfile you would like to upload
$epubfile = __DIR__.'/assets/test.epub';

// set the options in an array
$options = [
    'referenceid'          => '1234567890',
    'customername'         => 'customer',
    'customeremailaddress' => 'customer@example.com',
    'languagecode'         => 1033, // 1033 = English
];

// the type of request, in this case it's a request for a direct download of a watermarked epub
$type = 'direct';

try {
    // create a guzzle client
    $Guzzle = new Client();

    // create an options object
    $Options = new Options($options);

    // create the BooXtream Client
    $BooXtream = new BooXtreamClient($type, $Options, $credentials, $Guzzle);

    // set the epubfile
    $BooXtream->setFile($epubfile);

    // and send
    $Response = $BooXtream->send();

    // returns a Response object, containing a returned epub
    var_dump($Response);
} catch (Exception $e) {
    var_dump($e);
}
