<?php

namespace FapiMember\Api\V2\Endpoints;

use FapiMember\Api\V2\ApiController;
use FapiMember\Container\Container;
use FapiMember\FapiMemberPlugin;
use FapiMember\Library\SmartEmailing\Types\StringType;
use FapiMember\Model\Enums\Alert;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Service\ApiService;
use Throwable;
use WP_REST_Request;

class ApiConnectionsController
{
	private ApiService $apiService;
	private ApiController $apiController;

	public function __construct()
	{
		$this->apiService = Container::get(ApiService::class);
		$this->apiController = Container::get(ApiController::class);
	}

	public function list(): array
	{
		$clients = $this->apiService->getApiClients();
		$connectionsData = [];

		foreach ($clients as $client) {
			$connection = $client->getConnection();

			if ($connection->getApiKey() === null || $connection->getApiKey() === null) {
				continue;
			}

			$connectionData = array_merge($connection->toArray(), $client->getLicenceData());

			$connectionsData[] = $connectionData;
		}

		return $connectionsData;
	}

	public function getApiToken(): array
	{
		return ['apiToken' => $this->apiService->getApiToken()];
	}

	public function getStatusForAll(): array
	{
		return $this->apiService->getCredentialsStatuses();
	}

	public function create(array $body): void
	{
		$apiUser = $this->apiController->extractParamOrNull($body, 'api_user', StringType::class);
		$apiKey = $this->apiController->extractParamOrNull($body, 'api_key', StringType::class);

		if ( $apiKey === null || $apiUser === null ) {
			$this->apiController->callbackError([], Alert::API_FORM_EMPTY);
		}

		update_option(OptionKey::API_USER, $apiUser);
		update_option(OptionKey::API_KEY, $apiKey);

		$credentials = json_decode(get_option(OptionKey::API_CREDENTIALS));

		if (wp_list_filter( $credentials, ['username' => $apiUser])
		   && wp_list_filter($credentials, ['token' => $apiKey])
		) {
			$this->apiController->callbackError([], Alert::API_FORM_CREDENTIALS_EXIST);
		}

		if (empty($credentials)) {
			$credentials = [['username' => $apiUser, 'token' => $apiKey]];
		} elseif (count($credentials) < FapiMemberPlugin::CONNECTED_API_KEYS_LIMIT) {
			$credentials[] = ['username' => $apiUser, 'token' => $apiKey];
		} else {
			$this->apiController->callbackError([], Alert::API_FORM_TOO_MANY_CREDENTIALS);
		}

		update_option(OptionKey::API_CREDENTIALS, json_encode($credentials));
		$credentialsValid = $this->apiService->checkCredentials();
		update_option(OptionKey::API_CHECKED, $credentialsValid);
		$webUrl = rtrim(get_site_url(), '/' ) . '/';

		foreach ($this->apiService->getApiClients() as $apiClient) {
			$connection = $apiClient->getConnection();

			if ($connection === null) {
				$connection = $this->apiService->createConnection($webUrl, $apiClient);
				$apiClient->setConnection($connection);
			}
		}

		if ($credentialsValid) {
			$this->apiController->callbackResponse([], Alert::API_FORM_SUCCESS);
		} else {
			array_pop($credentials);
			update_option(OptionKey::API_CREDENTIALS, json_encode($credentials));
			update_option(
				OptionKey::API_CHECKED,
				$this->apiService->checkCredentials(),
			);

			$this->apiController->callbackError([], Alert::API_FORM_ERROR);
		}
	}


	public function remove(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$apiKey = $this->apiController->extractParam($body, 'api_key', StringType::class);

		try {
			$this->apiService->removeCredentials($apiKey);
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Failed to remove connection.",
			]);
		}

		$this->apiController->callbackResponse([], Alert::API_FORM_CREDENTIALS_REMOVED);
	}

}
