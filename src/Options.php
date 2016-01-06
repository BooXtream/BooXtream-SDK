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
				$this->options = array_replace_recursive(
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

		} catch ( \InvalidArgumentException $e ) {
			throw $e;
		}
	}

	/**
	 * @return array
	 */
	public function getMultipartArray() {
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
		if ( ! $this->checkString( 'customername' ) && ! $this->checkString( 'customeremailaddress' ) ) {
			throw new \InvalidArgumentException( 'at least one of customername or customeremailaddress is not set or empty' );
		}
		if ( ! $this->checkString( 'referenceid' ) ) {
			throw new \InvalidArgumentException( 'referenceid is not set or empty' );
		}
		if ( ! $this->checkString( 'languagecode' ) ) {
			throw new \InvalidArgumentException( 'languagecode is not set or empty' );
		}
	}

	private function checkXMLOptions() {
		if ( ! $this->checkInt( 'expirydays' ) ) {
			throw new \InvalidArgumentException( 'expirydays is not set or not an integer' );
		}
		if ( ! $this->checkInt( 'downloadlimit' ) ) {
			throw new \InvalidArgumentException( 'downloadlimit is not set or not an integer' );
		}
		if ( ! $this->checkBool( 'epub' ) ) {
			throw new \InvalidArgumentException( 'epub is not set or not a boolean' );
		}
		if ( ! $this->checkBool( 'kf8mobi' ) ) {
			throw new \InvalidArgumentException( 'kf8mobi is not set or not a boolean' );
		}
	}

	private function checkString( $name ) {
		return isset( $this->options[ $name ] ) && ! empty( $this->options[ $name ] );
	}

	/**
	 * @param string $name
	 */
	private function checkInt( $name ) {
		return isset( $this->options[ $name ] ) && is_int( $this->options[ $name ] );
	}

	/**
	 * @param string $name
	 */
	private function checkBool( $name ) {
		return isset( $this->options[ $name ] ) && is_bool( $this->options[ $name ] );
	}

}