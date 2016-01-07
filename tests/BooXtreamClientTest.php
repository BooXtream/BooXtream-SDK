<?php

namespace Icontact\BooXtreamClient\Tests;

use GuzzleHttp\Client;
use Icontact\BooXtreamClient\BooXtreamClient;
use Icontact\BooXtreamClient\Options;

class BooXtreamClientTest extends \PHPUnit_Framework_TestCase
{
    public function testSetEpubFile()
    {
        $guzzle      = new Client();
        $options     = new Options([
            'referenceid'  => 12345,
            'customername' => 'name',
            'languagecode' => 1033,
        ]);
        $credentials = [];

        $bx = new BooXtreamClient('epub', $options, $credentials, $guzzle);
        $this->assertTrue($bx->setEpubFile('./examples/assets/test.epub'));
    }
}
