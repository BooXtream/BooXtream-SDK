<?php

namespace Icontact\BooXtreamClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Post\PostFileInterface;

/**
 * Interface BooXtreamInterface
 * Use to connect to and use the BooXtream webservice
 */
interface BooXtreamInterface {
    /**
     * @param ClientInterface $guzzle
     * @param $username
     * @param $apikey
     */
    public function __construct(ClientInterface $guzzle, $username, $apikey);

    /**
     * @param string $type
     * @param PostFileInterface $file
     */
    public function createRequest($type, PostFileInterface $file = NULL);

    /**
     * @param string $storedfile
     * @return bool
     * @throws ClientException
     * @throws \Exception
     */
    public function setStoredFile($storedfile);

    /**
     * @param array $options
     */
    public function setOptions($options);

    /**
     * @return array
     */
    public function send();
} 