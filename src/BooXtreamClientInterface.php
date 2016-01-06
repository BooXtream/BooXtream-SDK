<?php

namespace Icontact\BooXtreamClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Interface BooXtreamInterface
 * Use to connect to and use the BooXtream webservice
 */
interface BooXtreamClientInterface {
	/**
	 * @param ClientInterface $Guzzle
	 * @param $username
	 * @param $apikey
	 */
	public function __construct( ClientInterface $Guzzle, $username, $apikey );

	/**
	 * @param string $type
	 */
	public function createRequest( $type );

	/**
	 * @param string $storedfile
	 *
	 * @return bool
	 */
	public function setStoredEpubFile( $storedfile );

	/**
	 * @param string $storedfile
	 *
	 * @return bool
	 */
	public function setStoredExlibrisFile( $storedfile );

	/**
	 * @param $options
	 */
	public function setOptions( $options );

	/**
	 * @param $file
	 */
	public function setEpubFile( $file );

	/**
	 * @param $file
	 */
	public function setExlibrisFile( $file );

	/**
	 * @return Response
	 */
	public function send();
} 