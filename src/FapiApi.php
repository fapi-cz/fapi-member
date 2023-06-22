<?php

namespace FapiMember;

use FapiMember\Utils\SecurityValidator;
use WP_Error;
use function json_decode;
use function json_encode;
use function sprintf;
use function wp_remote_request;

final class FapiApi {


	public $lastError = null;

	private $apiUser;

	private $apiKey;

	private $apiUrl;

	public function __construct( $apiUser, $apiKey, $apiUrl = 'https://api.fapi.cz/' ) {
		$this->apiUser = $apiUser;
		$this->apiKey  = $apiKey;
		$this->apiUrl  = $apiUrl;
	}

	/**
	 * @param int $id
	 * @return false|array<mixed>
	 */
	public function getInvoice( $id = '' ) {
		$response = $this->retryRequest(
			sprintf( '%sinvoices/%s', $this->apiUrl, $id )
		);

		if ( ! $response ) {
			return false;
		}

		return json_decode( $response['body'], true );
	}

	/**
	 * @return array<mixed>
	 */
	protected function createHeaders() {
		return array(
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
			'Authorization' => $this->createAuthHeader(),
		);
	}

	/**
	 * @return string
	 */
	protected function createAuthHeader() {
		return sprintf(
			'Basic %s',
			base64_encode(
				sprintf(
					'%s:%s',
					$this->apiUser,
					$this->apiKey
				)
			)
		);
	}

	/**
	 * @param WP_Error|array<mixed> $response
	 * @return mixed|string
	 */
	public function findErrorMessage( $response ) {
		if ( $response instanceof WP_Error ) {
			return $response->get_error_message();
		}

		if ( isset( $response['body'] ) ) {
			return $response['body'];
		}

		return '';
	}

	/**
	 * @param int $id
	 * @return false|array<mixed>
	 */
	public function getVoucher( $id ) {
		$response = $this->retryRequest(
			sprintf( '%svouchers/%s', $this->apiUrl, $id )
		);

		if ( ! $response ) {
			return false;
		}

		return json_decode( $response['body'], true );
	}

	/**
	 * @param string $code
	 * @return false|array<mixed>
	 */
	public function getItemTemplate( $code ) {
		$response = $this->retryRequest(
			sprintf( '%sitem_templates/?code=%s', $this->apiUrl, $code )
		);

		if ( ! $response ) {
			return false;
		}

		$data = json_decode( $response['body'], true );

		if ( ! isset( $data['item_templates'][0] ) ) {
			return false;
		}

		return $data['item_templates'][0];
	}

	/**
	 * @return bool
	 */
	public function checkCredentials() {
		$response = $this->retryRequest(
			sprintf( '%s', $this->apiUrl )
		);

		if ( ! $response ) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $webUrl
	 * @return array<mixed>|null|false
	 */
	public function findConnection( $webUrl ) {
		$response = $this->retryRequest(
			sprintf( '%sconnections?application=fapi-member&credentials_contains=%s', $this->apiUrl, $webUrl )
		);

		if ( ! $response ) {
			return false;
		}

		$data = json_decode( $response['body'], true );

		if ( isset( $data['connections'][0] ) ) {
			return $data['connections'][0];
		}

		return null;
	}

	/**
	 * @param string $webUrl
	 * @return array<mixed>|false
	 */
	public function createConnection( $webUrl ) {
		$response = wp_remote_request(
			sprintf( '%sconnections', $this->apiUrl ),
			array(
				'method'             => 'POST',
				'headers'            => $this->createHeaders(),
				'timeout'            => 30,
				'connection_timeout' => 30,
				'body'               => json_encode(
					array(
						'application' => 'fapi-member',
						'credentials' => array(
							'web_url' => $webUrl,
						),
					)
				),
			)
		);

		if ( $response instanceof WP_Error || $response['response']['code'] !== 201 ) {
			$this->lastError = $this->findErrorMessage( $response );

			return false;
		}

		return json_decode( $response['body'], true );
	}

	public function getForms() {
		$response = $this->retryRequest(
			sprintf( '%sforms', $this->apiUrl )
		);

		if ( ! $response ) {
			return false;
		}

		$data = json_decode( $response['body'], true );

		if ( isset( $data['forms'] ) ) {
			return $data['forms'];
		}

		return null;
	}

	/**
	 * @param array<mixed> $invoice
	 * @param int          $time
	 * @param string       $expectedSecurity
	 * @return bool
	 */
	public function isInvoiceSecurityValid( $invoice, $time, $expectedSecurity ) {
		return SecurityValidator::isInvoiceSecurityValid( $invoice, $time, $expectedSecurity );
	}

	/**
	 * @param array<mixed> $voucher
	 * @param array<mixed> $itemTemplate
	 * @param int          $time
	 * @param string       $expectedSecurity
	 * @return bool
	 */
	public function isVoucherSecurityValid( $voucher, $itemTemplate, $time, $expectedSecurity ) {
		return SecurityValidator::isVoucherSecurityValid( $voucher, $itemTemplate, $time, $expectedSecurity );
	}

	/**
	 * @param string      $remoteUrl
	 * @param string|null $bodyData
	 * @param int         $retries
	 * @return mixed
	 */
	private function retryRequest( $remoteUrl, $bodyData = null, $retries = 3 ) {
		$requestData = array(
			'method'             => 'GET',
			'headers'            => $this->createHeaders(),
			'timeout'            => 30,
			'connection_timeout' => 30,
		);

		$response = wp_remote_request( $remoteUrl, $requestData );

		if ( $response instanceof WP_Error || $response['response']['code'] !== 200 ) {
			if ( $retries > 0 ) {
				return $this->retryRequest( $remoteUrl, $bodyData, $retries - 1 );
			} else {
				$this->lastError = $this->findErrorMessage( $response );
				return false;
			}
		}

		return $response;
	}
	
	/**
	 * @return string
	 */
	public function getApiUser(){
		return $this->apiUser;
	}

	/**
	 * @return string
	 */
	public function getApiKey(){
		return $this->apiKey;
	}
}
