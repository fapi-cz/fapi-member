<?php

namespace FapiMember\Api\V2\Endpoints;

use FapiMember\Api\V2\ApiController;
use FapiMember\Container\Container;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Library\SmartEmailing\Types\StringType;
use FapiMember\Model\Enums\Alert;
use FapiMember\Model\Enums\Keys\MetaKey;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Repository\LevelRepository;
use FapiMember\Service\ApiService;
use FapiMember\Service\LevelOrderService;
use FapiMember\Service\LevelService;
use Throwable;
use WP_REST_Request;

class SectionsController
{
	private LevelRepository $levelRepository;
	private LevelService $levelService;
	private ApiService $apiService;
	private ApiController $apiController;
	private LevelOrderService $levelOrderService;

	public function __construct()
	{
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->levelService = Container::get(LevelService::class);
		$this->apiService = Container::get(ApiService::class);
		$this->apiController = Container::get(ApiController::class);
		$this->levelOrderService = Container::get(LevelOrderService::class);
	}

	public function list(): array
	{
		$sections = $this->levelService->getAllSectionsInOrder();
		$array = [];

		if ($sections === null) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Ordering of Levels is broken.",
			]);
		}

		foreach ($sections as $section) {
			$array[] = $section->toArray();
		}

		return $array;
	}

	public function get(int $id): array
	{
		$section = $this->levelRepository->getSectionById($id);

		if ($section === null) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "section with id: " . $id . " doesn't exist.",
			]);
		}

		return $section->toArray();
	}

	public function delete(int $id): void
	{
		try {
			$this->levelRepository->remove($id);
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "section/level with id: " . $id . " doesn't exist.",
			]);
		}


		$this->apiController->callbackResponse([], Alert::REMOVE_LEVEL_SUCCESSFUL);
	}

	public function create(array $data): void
	{
		$name = $this->apiController->extractParamOrNull($data, 'name', StringType::class);
		$parentId = StringType::extractOrNull($data, 'parent_id');

		if (trim($name) === '' || $name === null) {
			$this->apiController->callbackError([], Alert::SECTION_NAME_EMPTY);
		}

		try {
			$success = $this->levelService->create(trim($name), $parentId);
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "error while creating section/level",
			]);
		}

		if ($success) {
			$this->apiController->callbackSuccess();
		}

		$this->apiController->callbackError([], Alert::LEVEL_ALREADY_EXISTS);
	}

	public function update(int $id, array $data): void
	{

		try {
			$this->levelRepository->update($id, $data);
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "error while updating section/level",
			]);
		}

		$this->apiController->callbackSuccess();
	}

	public function reorder(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'id', IntType::class);
		$direction = $this->apiController->extractParam($body, 'direction', IntType::class);

		if ($direction !== 1 && $direction !== -1) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Direction value has to be 1 or -1.",
			]);
		}

		$success = $this->levelOrderService->reorder($levelId, $direction);

		if ($success) {
			$this->apiController->callbackSuccess();
		}

		$this->apiController->callbackError([], Alert::REORDER_FAILED);
	}

	public function getUnlocking(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'id', IntType::class);

		return [
			MetaKey::BUTTON_UNLOCK => $this->levelRepository->isButtonUnlock($levelId),
			MetaKey::TIME_UNLOCK => $this->levelRepository->getTimeUnlock($levelId),
			MetaKey::DATE_UNLOCK => $this->levelRepository->getDateUnlock($levelId),
			MetaKey::DAYS_TO_UNLOCK => $this->levelRepository->getDaysUnlock($levelId),
			MetaKey::HOUR_UNLOCK => $this->levelRepository->getHourUnlock($levelId),
		];
	}

	public function updateUnlocking(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'id', IntType::class);

		if (isset($body['unlocking']) && is_array($body['unlocking'])) {
			$this->levelRepository->updateSetUnlocking(
				$levelId,
				$body['unlocking'][MetaKey::BUTTON_UNLOCK] ?? null,
				$body['unlocking'][MetaKey::TIME_UNLOCK] ?? null,
				$body['unlocking'][MetaKey::DAYS_TO_UNLOCK] ?? null,
				$body['unlocking'][MetaKey::DATE_UNLOCK] ?? null,
				$body['unlocking'][MetaKey::HOUR_UNLOCK] ?? 0,
			);
		} else {
			$this->apiController->invalidParameterError('unlocking');
		}

		$this->apiController->callbackSettingsSaved();
	}

}
