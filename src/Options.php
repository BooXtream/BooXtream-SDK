<?php

namespace Icontact\BooXtreamClient;


class Options {

	private $defaultoptions = [
		'exlibris'      => false,
		'chapterfooter' => false,
		'disclaimer'    => false,
		'showdate'      => false,
	];

	private $options;

	/**
	 * Options constructor.
	 *
	 * @param array $options
	 */
	public function __construct( array $options ) {
		if ( ! is_array( $options ) ) {
			throw new \InvalidArgumentException( 'options expects an array' );
		}
		$this->options = array_replace_recursive( $this->defaultoptions, $options );
	}

	public function parseOptions( $xml ) {
		try {
			// Required stuff
			$this->checkRequiredOptions();

			// Check additional required options for XML requests
			if ( $xml ) {
				$this->options = array_replace_recursive (
					[
						'expirydays'    => 30,
				         'downloadlimit' => 3,
				         'epub'          => true,
				         'kf8mobi'       => false
					],
					$this->options
				);
				$this->checkXMLOptions();
			}

			// Optional stuff
			$this->checkOptionalOptions();

		} catch ( \InvalidArgumentException $e ) {
			throw $e;
		}
	}

	/**
	 * @return array
	 */
	public function getMultipartArray( ) {
		$multipart = [ ];
		foreach ( $this->options as $name => $contents ) {
			$multipart[] = [
				'name'     => $name,
				'contents' => (string) $contents
			];
		}
		return $multipart;
	}

	private function checkRequiredOptions() {
		// check required options
		if ( ! isset( $this->options['customername'] ) && ! isset( $this->options['customeremailaddress'] ) ) {
			throw new \InvalidArgumentException( 'required option customername or customeremailadress is not set' );
		}
		if ( ! isset( $this->options['referenceid'] ) ) {
			throw new \InvalidArgumentException( 'required option referenceid is not set' );
		}
		if ( ! isset( $this->options['languagecode'] ) ) {
			throw new \InvalidArgumentException( 'required option languagecode is not set' );
		}
	}

	private function checkXMLOptions() {
		if ( ! isset( $this->options['expirydays'] ) || ! is_int( $this->options['expirydays'] ) ) {
			throw new \InvalidArgumentException( 'expirydays is not set' );
		}
		if ( ! isset( $this->options['downloadlimit'] ) || ! is_int( $this->options['downloadlimit'] ) ) {
			throw new \InvalidArgumentException( 'downloadlimit is not set' );
		}
		if ( ! isset( $this->options['epub'] ) ) {
			throw new \InvalidArgumentException( 'epub is not set' );
		}
		if ( ! isset( $this->options['kf8mobi'] ) ) {
			throw new \InvalidArgumentException( 'kf8mobi is not set' );
		}

		// check options and translate booleans to 1 and 0
		$this->options['epub']    = $this->checkBool( 'epub', $this->options['epub'] );
		$this->options['kf8mobi'] = $this->checkBool( 'kf8mobi', $this->options['kf8mobi'] );
	}

	private function checkOptionalOptions() {
		// check optional (but default) options and translate booleans to 1 and 0
		$this->options['exlibris']      = $this->checkBool( 'exlibris', $this->options['exlibris'] );
		$this->options['chapterfooter'] = $this->checkBool( 'chapterfooter', $this->options['chapterfooter'] );
		$this->options['disclaimer']    = $this->checkBool( 'disclaimer', $this->options['disclaimer'] );
		$this->options['showdate']      = $this->checkBool( 'showdate', $this->options['showdate'] );
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

}