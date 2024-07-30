<?php

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Types\FormValueType;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Model\Enums\UserPermission;
use RuntimeException;

class FormService
{
	private SanitizationService $sanitizationService;

	public function __construct()
	{
		$this->sanitizationService = Container::get(SanitizationService::class);
	}

	public function loadPostValue(string $key, string $sanitizer, mixed $default = null): mixed
	{
		return $this->loadFormValue(RequestMethodType::POST, $key, $sanitizer, $default);
	}

	public function loadGetValue(string $key, string $sanitizer, mixed $default = null): mixed
	{
		return $this->loadFormValue(RequestMethodType::GET, $key, $sanitizer, $default);
	}

	public function loadFormValue(
		string $method,
		string $key,
		string $sanitizer,
		mixed $default = null,
	): mixed
	{
		switch ($method) {
			case RequestMethodType::GET:
				$values = $_GET;
				break;
			case RequestMethodType::POST:
				$values = $_POST;
				break;
			default:
				throw new RuntimeException('Not implemented method.');
		}

		$rawValue = (isset($values[$key])) ? $values[$key] : $default;

		if ($rawValue === null && $sanitizer !== FormValueType::CHECKBOX) {
			return null;
		}

		$sanitizerFunction = [$this->sanitizationService, $sanitizer];

		if (!is_callable($sanitizerFunction)) {
			throw new RuntimeException('Sanitizer should be callable.');
		}

		return $sanitizerFunction($rawValue, $default);
	}

	public function verifyNonce($hook): void
	{
		$nonce = sprintf( 'fapi_member_%s_nonce', $hook );

		if (!isset($_POST[$nonce])
			|| !wp_verify_nonce($_POST[$nonce], $nonce)
		) {
			wp_die(__('Zabezpečení formuláře neumožnilo zpracování, zkuste obnovit stránku a odeslat znovu.', 'fapi-member'));
		}
	}

	public function verifyNonceAndCapability($hook): void
	{
		$this->verifyNonce($hook);

		if (!current_user_can(UserPermission::REQUIRED_CAPABILITY)) {
			wp_die(__('Nemáte potřebná oprvánění.', 'fapi-member'));
		}
	}

}
