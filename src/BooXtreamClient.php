<?php
namespace Icontact\BooXtreamClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;

/**
 * Class BooXtreamClient
 * Use to connect to and use the BooXtream webservice
 */
class BooXtreamClient implements BooXtreamClientInterface {
	const BASE_URL = 'https://service.booxtream.com';

	private $Guzzle;
	private $authentication;
	private $type;
	private $Options;
	private $files;
	private $storedfiles;

	/**
	 * @param string $type
	 * @param Options $Options
	 * @param array $authentication
	 * @param ClientInterface $Guzzle
	 */
	public function __construct( $type, Options $Options, array $authentication, ClientInterface $Guzzle  ) {
		switch ( $type ) {
			case 'xml':
			case 'epub':
			case 'mobi':
				$this->type = $type;
				break;
			default:
				throw new \InvalidArgumentException( 'invalid type ' . $type );
		}

		$this->Guzzle         = $Guzzle;

		$this->Options        = $Options;
		$this->Options->parseOptions( $this->type === 'xml' );

		$this->authentication = $authentication;

		$this->files          = [ ];
		$this->storedfiles    = [ ];
	}

	/**
	 * @param string $file
	 */
	public function setEpubFile( $file ) {
		if ( isset ( $this->storedfiles['epubfile'] ) ) {
			throw new \RuntimeException( 'storedfile set but also trying to set local file' );
		}
		$this->files['epubfile'] = $this->checkFile( 'epubfile', $file );
	}

	/**
	 * @param string $file
	 */
	public function setExlibrisFile( $file ) {
		$this->files['exlibrisfile'] = $this->checkFile( 'exlibrisfile', $file );
	}

	/**
	 * @param string $file
	 *
	 * @return array
	 */
	private function checkFile( $name, $file ) {
		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			throw new \RuntimeException( 'file ' . $file . ' not found or readable while setting ' . $name );
		}

		return [
			'name'     => $name,
			'filename' => basename( $file ),
			'contents' => fopen( $file, 'r' )
		];
	}

	/**
	 * @param string $storedfile
	 */
	public function setStoredEpubFile( $storedfile ) {
		if ( isset( $this->files['epubfile'] ) ) {
			throw new \RuntimeException( 'epubfile set but also trying to set storedfile' );
		}

		// remove epub extension
		$pos = strrpos( strtolower( $storedfile ), '.epub' );
		if ( $pos ) {
			$storedfile = substr( $storedfile, 0, $pos );
		}
		$this->storedfiles['epubfile'] = $this->checkStoredFile( $storedfile );
	}

	/**
	 * @param string $storedfile
	 */
	public function setStoredExlibrisFile( $storedfile ) {
		if ( isset( $this->files['exlibrisfile'] ) ) {
			throw new \RuntimeException( 'exlibrisfile set but also trying to set storedfile' );
		}

		$this->storedfiles['exlibrisfile'] = $this->checkStoredFile( $storedfile );
	}

	/**
	 * @param string $storedfile
	 *
	 * @return string
	 */
	private function checkStoredFile( $storedfile ) {
		try {
			// check if stored file exists
			$response = $this->Guzzle->request(
				'GET',
				self::BASE_URL . '/storedfiles/' . $storedfile,
				[
					'auth'  => $this->authentication,
					'query' => [
						'exists' => ''
					]
				]
			);
			if ( $response->getStatusCode() === 200 ) {
				return $storedfile;
			}

			throw new \RuntimeException( 'unknown error occured while checking storedfile ' . $storedfile );

		} catch ( ClientException $e ) {
			if ( $e->getCode() === 404 ) {
				throw new \RuntimeException( 'storedfile ' . $storedfile . ' does not exist' );
			}
			throw $e;
		}
	}

	public function send() {
		if ( ! isset( $this->storedfiles['epubfile'] ) && ! isset( $this->files['epubfile'] ) ) {
			throw new \RuntimeException( 'storedfile or epubfile not set' );
		}

		$multipart = $this->createMultipart();

		// set action
		$action = self::BASE_URL . '/booxtream.' . $this->type;
		if ( isset( $this->storedfiles['epubfile'] ) ) {
			$action = self::BASE_URL . '/storedfiles/' . $this->storedfiles['epubfile'] . '.' . $this->type;
		}

		try {
			$response = $this->Guzzle->request(
				'POST',
				$action,
				[
					'auth'      => $this->authentication,
					'multipart' => $multipart
				]
			);
		} catch ( ClientException $e ) {
			$response = $e->getResponse();
		}

		return $response;
	}

	private function createMultipart() {
		$multipart = $this->Options->getMultipartArray();

		if ( isset ( $this->storedfiles['exlibrisfile'] ) ) {
			$multipart[] = [
				'name'     => 'exlibrisfile',
				'contents' => $this->storedfiles['exlibrisfile']
			];
		}

		if ( isset( $this->files['epubfile'] ) ) {
			$multipart[] = $this->files['epubfile'];
		}

		if ( isset( $this->files['exlibrisfile'] ) ) {
			$multipart[] = $this->files['exlibrisfile'];
		}

		return $multipart;
	}

}