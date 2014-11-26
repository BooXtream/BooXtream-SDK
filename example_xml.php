<?php

require('vendor/autoload.php');

use \Icontact\BooXtream\BooXtreamClient;
use \GuzzleHttp\Client;

$guzzle = new Client();

$BooXtream = new BooXtreamClient($guzzle, 'test', 'test');
$BooXtream->createRequest();