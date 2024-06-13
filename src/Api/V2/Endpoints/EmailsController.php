<?php

namespace FapiMember\Api\V2\Endpoints;

use FapiMember\Api\V2\ApiController;
use FapiMember\Container\Container;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Model\Enums\Types\EmailType;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Repository\EmailRepository;
use FapiMember\Service\ApiService;
use Throwable;
use WP_REST_Request;

class EmailsController
{
	private EmailRepository $emailRepository;
	private ApiService $apiService;
	private ApiController $apiController;

	public function __construct()
	{
		$this->emailRepository = Container::get(EmailRepository::class);
		$this->apiService = Container::get(ApiService::class);
		$this->apiController = Container::get(ApiController::class);
	}

	public function getForLevel(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'level_id', IntType::class);

		return $this->emailRepository->getTemplatesForLevel($levelId);
	}

	public function updateForLevel(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'level_id', IntType::class);

		$emails = [];
		if (isset($body['emails']) && is_array($body['emails'])) {
			$emails = $body['emails'];
		} else {
			$this->apiController->invalidParameterError('emails');
		}

		try {
			foreach ($emails as $type => $email) {
				if (EmailType::isValidValue($type)) {
					$this->emailRepository->update($levelId, $type, $email['subject'], $email['body']);
				}
			}
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Failed to update service pages",
			]);
		}

		$this->apiController->callbackSettingsSaved();
	}

}
