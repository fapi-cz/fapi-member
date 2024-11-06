<?php

namespace FapiMember\Utils;

use FapiMember\Model\ApiConnection;
use FapiMember\Model\Enums\Keys\OptionKey;
use WP_Error;

class ApiClient
{
	private ApiConnection|null $connection;

	private string $apiUrl;

	private string|null $lastError;

	public function __construct(ApiConnection|null $apiConnection = null)
	{
		$this->apiUrl = get_option(OptionKey::API_URL, 'https://api.fapi.cz/');
		$this->lastError = null;
		$this->connection = $apiConnection;
	}

	public function retryRequest($remoteUrl, $bodyData = null, $retries = 3): WP_Error|bool|array
	{
		$requestData = [
			'method'             => 'GET',
			'headers'            => $this->getHeaders(),
			'timeout'            => 30,
			'connection_timeout' => 30,
		];

		$this->setLastError(null);
		$response = wp_remote_request($remoteUrl, $requestData);

		if ($response instanceof WP_Error || $response['response']['code'] !== 200) {
			if ($retries > 0) {
				return $this->retryRequest($remoteUrl, $bodyData, $retries - 1);
			} else {
				$this->setLastError($this->findErrorMessage($response));
				return false;
			}
		}

		return $response;
	}

	/** @return array<string> */
	public function getHeaders(): array
	{
		return array(
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
			'Authorization' => $this->getAuthHeader(),
		);
	}

	protected function getAuthHeader(): string
	{
		return sprintf(
			'Basic %s',
			base64_encode(
				sprintf(
					'%s:%s',
					$this->connection->getApiUser(),
					$this->connection->getApiKey()
				)
			)
		);
	}

	public function findErrorMessage(WP_Error|array $response): string
	{
		if ($response instanceof WP_Error) {
			return $response->get_error_message();
		}

		if (isset($response['body'])) {
			return $response['body'];
		}

		return '';
	}

	public function checkCredentials(): bool
	{
		$response = $this->retryRequest($this->apiUrl);

		if (!$response) {
			return false;
		}

		return true;
	}

	public function getLicenceData(): array
	{
		$response = $this->retryRequest($this->apiUrl . 'users/get-fm-licence');

		if (!$response) {
			return [];
		}

		return json_decode($response['body'], true);
	}

	public function getVoucher(int $id): false|array
	{
		$response = $this->retryRequest($this->apiUrl . 'vouchers/' . $id);

		if (!$response) {
			return false;
		}

		return json_decode($response['body'], true);
	}

	public function getInvoice(int $id): false|array
	{
		$response = $this->retryRequest($this->apiUrl . 'invoices/' . $id);

		if (!$response) {
			return false;
		}

		return json_decode($response['body'], true);
	}

	public function getAllRepaymentInvoices(int $partialParent): false|array
	{
		$response = $this->retryRequest(
			$this->apiUrl . 'invoices/?partial_parent=' . $partialParent,
		);

		if (!$response) {
			return false;
		}

		return json_decode($response['body'], true);
	}

	public function getForms(): mixed
	{
		$response = $this->retryRequest($this->apiUrl . 'forms');

		if (!$response) {
			return false;
		}

		$data = json_decode($response['body'], true);

		if (isset( $data['forms'])) {
			return $data['forms'];
		}

		return null;
	}

	public function getApiUrl(): string
	{
		return $this->apiUrl;
	}

	public function getConnection(): ApiConnection|null
	{
		return $this->connection;
	}

	public function setConnection(ApiConnection $connection): void
	{
		$this->connection = $connection;
	}

	public function getLastError(): string|null
	{
		return $this->lastError;
	}

	public function setLastError(string|null $lastError): void
	{
		$this->lastError = $lastError;
	}

}
