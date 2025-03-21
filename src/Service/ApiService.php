<?php declare(strict_types=1);

namespace FapiMember\Service;

use FapiMember\FapiMemberPlugin;
use FapiMember\Model\ApiConnection;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Utils\ApiClient;
use WP_Error;
use const FAPI_MEMBER_PLUGIN_VERSION;

class ApiService
{
	/** @return array<ApiClient> */
	public function getApiClients(): array
	{
		$credentials = get_option(OptionKey::API_CREDENTIALS, null);

		if (empty($credentials)) {
			$credentials = [
				[
					'username' => null,
					'token' => null,
				],
			];
		} else {
			$credentials = json_decode($credentials, true);
		}

		$fapiConnections = [];

		foreach ($credentials as $credential) {
			$fapiConnections[] = new ApiClient(
				new ApiConnection($credential['username'], $credential['token'])
			);
		}

		return $fapiConnections;
	}

	/** @return array<ApiConnection> */
	public function getApiConnections(): array
	{
		$clients = $this->getApiClients();
		$connections = [];

		foreach ($clients as $client) {
			$connections[] = $client->getConnection();
		}

		return $connections;
	}

	public function removeCredentials(string $token): void
	{
		$credentials = json_decode(get_option(OptionKey::API_CREDENTIALS)) ?? [];

		foreach ($credentials as $user => $credential) {
			if  ($credential->token === $token) {
				unset( $credentials[$user]);
			}
		}

		if (empty($credentials)) {
			update_option(OptionKey::API_CREDENTIALS, '');
		} else {
			update_option(OptionKey::API_CREDENTIALS, json_encode(array_values($credentials)));
		}

		$credentialsOk = $this->checkCredentials();
		update_option(OptionKey::API_CHECKED, $credentialsOk);
	}

	/** @return array<string> */
	public function getLastErrors(): array
	{
		$lastErrors = [];

		foreach ($this->getApiClients() as $apiClient) {
			$lastError = $apiClient->getLastError();

			if ($lastError !== null) {
				$lastErrors[$apiClient->getConnection()->getApiUser()][] = $lastError;
			}
		}

		return $lastErrors;
	}

	public function getVoucher(int $id): false|array
	{
		foreach ($this->getApiClients() as $apiClient) {
			$response = $apiClient->getVoucher($id);

			if (is_array($response) && !isset($response['error'])) {
				return $response;
			}
		}

		return false;
	}

	public function getInvoice(int $id): false|array
	{
		foreach ($this->getApiClients() as $apiClient) {
			$response = $apiClient->getInvoice($id);

			if (is_array($response) && ! isset($response['error'])) {
				return $response;
			}
		}

		return false;
	}

	public function getAllInvoicesInRepayment(int $invoiceId): false|array
	{
		$invoice = $this->getInvoice($invoiceId);
		$partialParent = (int) $invoice['partial_parent'];

		foreach ($this->getApiClients() as $apiClient) {
			$response = $apiClient->getAllRepaymentInvoices($partialParent);

			if ($response !== false && isset($response['invoices']) && count($response['invoices']) > 0) {
				return $response['invoices'];
			}
		}

		return false;
	}

	public function checkCredentials(): bool
	{
		$credentialsOk = true;
		$clients = $this->getApiClients();

		if (count($clients) === 0) {
			return false;
		}

		foreach ($clients as $client) {
			$credentialsOk = $credentialsOk && $client->checkCredentials();
		}

		return $credentialsOk;
	}

	public function checkLicence(): bool
	{
		$clients = $this->getApiClients();
		$licenceActive = false;

		if (count($clients) === 0) {
			return false;
		}

		foreach ($clients as $client) {
			$licenceData = $client->getLicenceData();

			if (isset($licenceData['active']) && $licenceData['active'] === true) {
				$licenceActive = true;
			}
		}

		return $licenceActive;
	}

	public function getCredentialsStatuses(): array
	{
		$clients = $this->getApiClients();

		if (count($clients) === 0) {
			return [];
		}

		$statuses = [];

		foreach ($clients as $client) {
			$statuses[$client->getConnection()->getApiKey()] = $client->checkCredentials();
		}

		return $statuses;
	}

	public function getApiToken(): string
	{
		return get_option(OptionKey::TOKEN, '');
	}

	public function createConnection($webUrl, ApiClient $apiClient): ApiConnection|null
	{
		$response = wp_remote_request(
			sprintf( '%sconnections', $apiClient->getApiUrl()),
			array(
				'method'             => 'POST',
				'headers'            => $apiClient->getHeaders(),
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

		if ($response instanceof WP_Error || $response['response']['code'] !== 201) {
			$apiClient->setLastError($apiClient->findErrorMessage($response));

			return null;
		}

		$credential = json_decode($response['body'], true );

		return new ApiConnection($credential['username'], $credential['token']);
	}

	public function areApiCredentialsSet() {
		return get_option(OptionKey::API_CHECKED, false);
	}

	/** @deprecated  */ //used only in API V1
	public function callbackError(array $data): never
	{
		wp_send_json_error(
			array(
				$data,
				FapiMemberPlugin::FAPI_MEMBER_PLUGIN_VERSION_KEY => FAPI_MEMBER_PLUGIN_VERSION,
			),
			400
		);

		die;
	}

}
