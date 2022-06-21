<?php

namespace FapiMember;

use function json_encode;


final class FapiClients {


	/**
	 * @var array<FapiApi>&FapiApi[]
	 */
	private $fapiApis;

	/**
	 * @param array<FapiApi> &FapiApi[] $fapiApis
	 */
	public function __construct( $fapiApis ) {
		$this->fapiApis = $fapiApis;
	}

	/**
	 * @return array<FapiApi>
	 */
	public function getFapiApis() {
		return $this->fapiApis;
	}

	/**
	 * @param  int $id
	 * @return false|array<mixed>
	 */
	public function getInvoice( $id ) {
		foreach ( $this->fapiApis as $fapiApi ) {
			$response = $fapiApi->getInvoice( $id );

			if ( is_array( $response ) ) {
				return $response;
			}
		}

		return false;
	}


	/**
	 * @param  int $id
	 * @return false|array<mixed>
	 */
	public function getVoucher( $id ) {
		foreach ( $this->fapiApis as $fapiApi ) {
			$response = $fapiApi->getVoucher( $id );

			if ( is_array( $response ) ) {
				return $response;
			}
		}

		return false;
	}

	/**
	 * @param  string $code
	 * @return false|array<mixed>
	 */
	public function getItemTemplate( $code ) {
		foreach ( $this->fapiApis as $fapiApi ) {
			$response = $fapiApi->getItemTemplate( $code );

			if ( is_array( $response ) ) {
				return $response;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function checkCredentials() {
		$credentialsOk = true;

		foreach ( $this->fapiApis as $fapiApi ) {
			$credentialsOk = $credentialsOk && $fapiApi->checkCredentials();
		}

		return $credentialsOk;
	}

	/**
	 * @return string
	 */
	public function getLastErrors() {
		$out = array();

		foreach ( $this->fapiApis as $fapiApi ) {
			$lastError = $fapiApi->lastError;

			if ( $lastError !== null ) {
				$out[] = $lastError;
			}
		}

		return (string) json_encode( $out );
	}

}
