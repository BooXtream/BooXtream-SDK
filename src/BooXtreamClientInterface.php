<?php

namespace Icontact\BooXtreamClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Post\PostFileInterface;
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
     * @param PostFileInterface $file
     */
    public function createRequest($type);

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
     * @param $file
     * @return mixed
     */
    public function setEpubFile($file);

    /**
     * @param $file
     * @return mixed
     */
    public function setExlibrisFile($file);

    /**
     * @return ResponseInterface
     */
    public function send();
} 