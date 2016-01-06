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
	private $options;
	private $files;
	private $storedfiles;

	/**
	 * @param ClientInterface $Guzzle
	 * @param string $username
	 * @param string $apikey
	 */
	public function __construct( ClientInterface $Guzzle, $username, $apikey ) {
		$this->Guzzle         = $Guzzle;
		$this->authentication = [ $username, $apikey ];
		$this->files          = [ ];
		$this->storedfiles    = [ ];
	}

	/**
	 * @param string $type
	 */
	public function createRequest( $type ) {
		switch ( $type ) {
			case 'xml':
			case 'epub':
			case 'mobi':
				$this->type = $type;
				break;
			default:
				throw new \InvalidArgumentException( 'invalid type ' . $type );
		}
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
	 * @deprecated
	 */
	public function setStoredFile( $storedfile ) {
		trigger_error('setStoredFile has been deprecated in favor of setStoredEpubFile, please change your code.', E_USER_NOTICE);
		$this->setStoredEpubFile( $storedfile );
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
			} else {
				throw new \RuntimeException( 'unknown error occured while checking storedfile ' . $storedfile );
			}
		} catch ( ClientException $e ) {
			if ( $e->getCode() === 404 ) {
				throw new \RuntimeException( 'storedfile ' . $storedfile . ' does not exist' );
			}
			throw $e;
		}
	}

	/**
	 * @param array $options
	 */
	public function setOptions( $options ) {
		if ( ! is_array( $options ) ) {
			throw new \InvalidArgumentException( 'options expects an array' );
		}
		$options       = array_replace_recursive( $this->getDefaultOptions(), $options );
		$this->options = $this->parseOptions( $options );
	}

	public function send() {
		if ( ! isset( $this->storedfiles['epubfile'] ) && ! isset( $this->files['epubfile'] ) ) {
			throw new \RuntimeException( 'storedfile or epubfile not set' );
		}
		if ( is_null( $this->options ) ) {
			throw new \RuntimeException( 'options not set' );
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
		$multipart = [ ];

		foreach ( $this->options as $name => $contents ) {
			$multipart[] = [
				'name'     => $name,
				'contents' => (string) $contents
			];
		}

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

	/**
	 * @param array $options
	 *
	 * @return array
	 */
	private function parseOptions( $options ) {
		try {
			// check required options
			if ( ! isset( $options['customername'] ) && ! isset( $options['customeremailaddress'] ) ) {
				throw new \InvalidArgumentException( 'required option customername or customeremailadress is not set' );
			}
			if ( ! isset( $options['referenceid'] ) ) {
				throw new \InvalidArgumentException( 'required option referenceid is not set' );
			}
			if ( ! isset( $options['languagecode'] ) ) {
				throw new \InvalidArgumentException( 'required option languagecode is not set' );
			}

			// check additional required options for XML requests
			if ( $this->type === 'xml' ) {
				if ( ! isset( $options['expirydays'] ) || ! is_int( $options['expirydays'] ) ) {
					throw new \InvalidArgumentException( 'expirydays is not set' );
				}
				if ( ! isset( $options['downloadlimit'] ) || ! is_int( $options['downloadlimit'] ) ) {
					throw new \InvalidArgumentException( 'downloadlimit is not set' );
				}
				if ( ! isset( $options['epub'] ) ) {
					throw new \InvalidArgumentException( 'epub is not set' );
				}
				if ( ! isset( $options['kf8mobi'] ) ) {
					throw new \InvalidArgumentException( 'kf8mobi is not set' );
				}

				// check options and translate booleans to 1 and 0
				$options['epub']    = $this->checkBool( 'epub', $options['epub'] );
				$options['kf8mobi'] = $this->checkBool( 'kf8mobi', $options['kf8mobi'] );
			}

			// check optional (but default) options and translate booleans to 1 and 0
			if ( ! isset ( $this->files['exlibrisfile'] ) && ! isset ( $this->storedfiles['exlibrisfile'] ) ) {
				$options['exlibris'] = $this->checkBool( 'exlibris', $options['exlibris'] );
			} else {
				// force this, there is no use to setting an exlibrisfile without setting exlibris to true
				$options['exlibris'] = 1;
			}
			$options['chapterfooter'] = $this->checkBool( 'chapterfooter', $options['chapterfooter'] );
			$options['disclaimer']    = $this->checkBool( 'disclaimer', $options['disclaimer'] );
			$options['showdate']      = $this->checkBool( 'showdate', $options['showdate'] );

			return $options;
		} catch ( \InvalidArgumentException $e ) {
			throw $e;
		}
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return int
	 */
	private function checkBool( $name, $value ) {
		if ( ! is_bool( $value ) ) {
			throw new \InvalidArgumentException( $name . ' is set incorrectly' );
		}

		return $value ? 1 : 0;
	}

	/**
	 * @return array
	 */
	private function getDefaultOptions() {
		$options = [
			'exlibris'      => false,
			'chapterfooter' => false,
			'disclaimer'    => false,
			'showdate'      => false
		];

		if ( $this->type === 'xml' ) {
			$options = array_merge( $options, [
				'expirydays'    => 30,
				'downloadlimit' => 3,
				'epub'          => true,
				'kf8mobi'       => false
			] );
		}

		return $options;
	}
}