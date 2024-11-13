<?php declare(strict_types=1);

namespace FapiMember\Api\V2;

use FapiMember\FapiMemberPlugin;
use FapiMember\Library\SmartEmailing\Types\Arrays;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Model\Enums\Alert;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Utils\AlertProvider;
use Throwable;
use WP_REST_Request;

class ApiController
{
	private array $freeAccessEndpoints = [
		'memberships' => ['unlockLevelForLoggedInUser'],
	];

	public function handleRequest(WP_REST_Request $request): void
	{
		$params = $request->get_query_params();
		$controllerName = str_replace('/fapi/v2/', '', $params['rest_route']);
		$route = 'FapiMember\\Api\\V2\\Endpoints\\' . ucfirst($controllerName) . 'Controller';


		if (isset($params['action'])) {
			$action = $params['action'];
		} elseif (isset($params['id'])) {
			$action = 'get';
		} else {
			$action = 'list';
		}

		$actionFunction = 'handle' . ucfirst($action);

		if (
			!isset($this->freeAccessEndpoints[$controllerName]) ||
			!in_array($action, $this->freeAccessEndpoints[$controllerName])
		) {
			$nonce = $request->get_header('X-WP-Nonce');

			if (!wp_verify_nonce($nonce, 'wp_rest')) {
				$this->callbackError([
					'class'=> self::class,
					'description' => "Permission denied.",
				]);
			}
		}

		try {
			$controller = new $route();
		} catch (Throwable) {
			$this->callbackError([
				'class'=> self::class,
				'description' => "specified endpoint doesn't exist.",
			]);
		}

		if (!is_callable([$controller, $action])) {
			$this->callbackError([
				'class'=> $controller::class,
				'description' => "specified action doesn't exist. Action: " . $action,
			]);
		}


		if (is_callable([$this, $actionFunction])) {
			$data = $this->$actionFunction($request, $controller, $action);
		} else {
			$data = $controller->$action($request);
		}

		wp_send_json($data);
	}

	private function handleList(WP_REST_Request $request, mixed $controller, string $action): mixed
	{
		if ($request->get_method() !== RequestMethodType::GET) {
			$this->wrongMethodError(RequestMethodType::GET);
		}

		return $controller->$action();
	}


	private function handleGet(WP_REST_Request $request, mixed $controller, string $action): mixed
	{
		if ($request->get_method() !== RequestMethodType::GET) {
			$this->wrongMethodError(RequestMethodType::GET);
		}

		$params = $request->get_params();

		if (!isset($params['id'])) {
			$this->missingParameterError('id');
		}

		try {
			$id = IntType::extract($params, 'id');
		} catch (Throwable) {
			$this->invalidParameterError('id');
		}

		return $controller->$action($id);
	}

	private function handleDelete(WP_REST_Request $request, mixed $controller, string $action): bool
	{
		if ($request->get_method() !== RequestMethodType::POST) {
			$this->wrongMethodError(RequestMethodType::POST);
		}

		$body = json_decode($request->get_body(), true);

		$id = $this->extractParam($body, 'id', IntType::class);

		return $controller->$action($id);
	}

	private function handleCreate(WP_REST_Request $request, mixed $controller, string $action): bool
	{
		if ($request->get_method() !== RequestMethodType::POST) {
			$this->wrongMethodError(RequestMethodType::POST);
		}

		$body = json_decode($request->get_body(), true);

		return $controller->$action($body);
	}

	private function handleUpdate(WP_REST_Request $request, mixed $controller, string $action): bool
	{
		if ($request->get_method() !== RequestMethodType::POST) {
			$this->wrongMethodError(RequestMethodType::POST);
		}

		$body = json_decode($request->get_body(), true);

		$id = $this->extractParam($body, 'id', IntType::class);
		$data = $this->extractParam($body, 'data', Arrays::class);

		return $controller->$action($id, $data);
	}


	public function extractParam(array $array, string $key, string $type): mixed
	{
		if (!isset($array[$key])) {
			$this->missingParameterError($key);
		}


		$param = $this->extractParamOrNull($array, $key, $type);

		if ($param === null) {
			$this->invalidParameterError($key);

		} else {
			return $param;
		}
	}

	public function extractParamOrNull(array $array, string $key, string $type): mixed
	{
		if (!isset($array[$key])) {
			return null;
		}

		try {
			return $type::extractOrNull($array, $key);
		} catch (Throwable) {
			return null;
		}
	}

	public function callbackSettingsSaved(mixed $data = []): never
	{
		$this->callbackResponse($data, Alert::SETTINGS_SAVED);
	}

	public function callbackError(array $data, string $alert = Alert::INTERNAL_ERROR): never
	{
		$data[FapiMemberPlugin::FAPI_MEMBER_PLUGIN_VERSION_KEY ] = FAPI_MEMBER_PLUGIN_VERSION;
		$data['alert'] = AlertProvider::getError($alert);

		wp_send_json_error(
			$data,
			400,
		);

		die;
	}

	/**
	 * @param array $data
	 * @return never
	 */
	public function callbackSuccess($data = []): never
	{
		wp_send_json_success(
			$data,
			200,
		);

		die;
	}

	public function callbackResponse(array $data, string $alert): never
	{
		$data[FapiMemberPlugin::FAPI_MEMBER_PLUGIN_VERSION_KEY ] = FAPI_MEMBER_PLUGIN_VERSION;
		$data['alert'] = AlertProvider::getError($alert);

		wp_send_json_success(
			$data,
			200,
		);

		die;
	}


	public function wrongMethodError(string $method): never
	{
		$this->callbackError([
			'class'=> ApiController::class,
			'description' => "wrong request method. Expecting: " . $method,
		]);
	}

	public function missingParameterError(string $parameter): never
	{
		$this->callbackError([
			'class'=> self::class,
			'description' => "missing parameter '" . $parameter . "'",
		]);
	}

	public function invalidParameterError(string $parameter): never
	{
		$this->callbackError([
			'class'=> self::class,
			'description' => "invalid parameter '" . $parameter . "'",
		]);
	}

	public function checkRequestMethod(\WP_REST_Request $request, string $method): void
	{
		if ($request->get_method() !== $method) {
			$this->wrongMethodError($method);
		}
	}


}
