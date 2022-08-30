<?php

namespace Icontact\BooXtreamClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Interface BooXtreamInterface
 * Use to connect to and use the BooXtream webservice.
 */
interface BooXtreamClientInterface
{
    /**
     * @param  string  $type
     * @param  Options  $options
     * @param  array  $authentication
     * @param  ClientInterface  $guzzle
     */
    public function __construct($type, Options $options, array $authentication, ClientInterface $guzzle);

    /**
     * @param  string  $storedfile
     * @return bool
     */
    public function setStoredEpubFile($storedfile);

    /**
     * @param  string  $storedfile
     * @return bool
     */
    public function setStoredExlibrisFile($storedfile);

    /**
     * @param $file
     */
    public function setEpubFile($file);

    /**
     * @param $file
     */
    public function setExlibrisFile($file);

    /**
     * @return Response
     */
    public function send();
}
