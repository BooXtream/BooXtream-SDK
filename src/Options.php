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
		if(!$this->checkString('customername', false) && !$this->checkString('customeremailaddress', false)) {
			throw new \InvalidArgumentException( 'at least one of customername or customeremailaddress is not set or empty' );
		}
		$this->checkString('referenceid');
		$this->checkString('languagecode');
	}

	private function checkXMLOptions() {
		$this->checkInt('expirydays');
		$this->checkInt('downloadlimit');

		$this->checkBool( 'epub' );
		$this->checkBool( 'kf8mobi' );
	}

	private function checkOptionalOptions() {
		$this->checkBool( 'exlibris', false );
		$this->checkBool( 'chapterfooter', false );
		$this->checkBool( 'disclaimer', false );
		$this->checkBool( 'showdate', false );
	}

	private function checkString ( $name, $strict=true ) {
		if ( ! isset( $this->options[$name] ) || empty( $this->options[$name] )) {
			if( $strict ) throw new \InvalidArgumentException( $name.' is not set or empty' );
			return false;
		}
		return true;
	}

	/**
	 * @param string $name
	 */
	private function checkInt ( $name, $strict=true ) {
		if ( ! isset( $this->options[$name]) || ! is_int ( $this->options[$name] ) ) {
			if( $strict ) throw new \InvalidArgumentException( $name.' is not set or not an integer' );
			return false;
		}
		return true;
	}

	/**
	 * @param string $name
	 */
	private function checkBool( $name, $strict=true ) {
		if ( ! isset( $this->options[$name] ) || ! is_bool( $this->options[$name] ) ) {
			if( $strict ) throw new \InvalidArgumentException( $name . ' is not set or not a boolean' );
			return false;
		}
		return true;
	}

}