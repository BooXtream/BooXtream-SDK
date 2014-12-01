<?php

namespace Icontact\BooXtreamClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Interface BooXtreamInterface
 * Use to connect to and use the BooXtream webservice
 */
interface BooXtreamClientInterface {
    /**
     * @param ClientInterface $guzzle
     * @param $username
     * @param $apikey
     */
    public function __construct(ClientInterface $guzzle, $username, $apikey);

    /**
     * @param string $type
     */
    public function createRequest($type);

    /**
     * @param string $storedfile
     * @return bool
     */
    public function setStoredFile($storedfile);

    /**
     * @param $options
     */
    public function setOptions($options);

    /**
     * @param $file
     */
    public function setEpubFile($file);

    /**
     * @param $file
     */
    public function setExlibrisFile($file);

    /**
     * @return ResponseInterface
     */
    public function send();
} 