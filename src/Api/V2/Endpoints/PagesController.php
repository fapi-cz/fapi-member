<?php

namespace FapiMember\Api\V2\Endpoints;

use FapiMember\Api\V2\ApiController;
use FapiMember\Container\Container;
use FapiMember\Library\SmartEmailing\Types\Arrays;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Model\Enums\Keys\SettingsKey;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Model\Enums\Types\ServicePageType;
use FapiMember\Model\Settings;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\PageRepository;
use FapiMember\Repository\SettingsRepository;
use Throwable;
use WP_REST_Request;

class PagesController
{
	private PageRepository $pageRepository;
	private SettingsRepository $settingsRepository;
	private LevelRepository $levelRepository;
	private ApiController $apiController;

	public function __construct()
	{
		$this->pageRepository = Container::get(PageRepository::class);
		$this->settingsRepository = Container::get(SettingsRepository::class);
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->apiController = Container::get(ApiController::class);
	}

	public function list(): array
	{
		$pages = $this->pageRepository->getAllPages();
		$pageData = [];

		foreach ($pages as $page) {
			$pageData[] = $page->toArray();
		}

		return $pageData;
	}

	public function listWithCpts(): array
	{
		$pages = $this->pageRepository->getAllPages(true);
		$pageData = [];

		foreach ($pages as $page) {
			$pageData[] = $page->toArray();
		}

		return $pageData;
	}

	public function getIdsByAllLevels(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::GET);

		$levels = $this->levelRepository->getAllAsLevels();
		$pageIds = [];

		foreach ($levels as $level) {
			$pageIds[$level->getId()] = $this->pageRepository->getPageIdsByLevelId($level->getId());
		}

		return $pageIds;
	}

	public function getIdsByLevel(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'level_id', IntType::class);

		return $this->pageRepository->getPageIdsByLevelId($levelId);
	}


	public function updatePagesForLevel(WP_REST_Request $request): bool
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'level_id', IntType::class);
		$pages = $this->apiController->extractParam($body, 'pages', Arrays::class);


		try{
			$this->pageRepository->updatePagesForLevel($levelId, $pages);
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Page update failed",
			]);
		}

		$this->apiController->callbackSettingsSaved();
	}

	public function addPagesToLevel(WP_REST_Request $request): bool
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'level_id', IntType::class);
		$pages = $this->apiController->extractParam($body, 'pages', Arrays::class);

		try{
			$data = [];
			$data['pages'] = $this->pageRepository->addPages($levelId, $pages);
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Page update failed",
			]);
		}

		$this->apiController->callbackSettingsSaved($data);
	}

	public function getServicePagesByLevel(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'level_id', IntType::class);

		return [
			ServicePageType::NO_ACCESS => $this->pageRepository->getNoAccessPageId($levelId),
			ServicePageType::LOGIN => $this->pageRepository->getLoginPageId($levelId),
			ServicePageType::AFTER_LOGIN => $this->pageRepository->getAfterLoginPageId($levelId),
		];

	}

	public function updateServicePagesForLevel(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'level_id', IntType::class);

		$pages = [];

		if (isset($body['pages']) && is_array($body['pages'])) {
			$pages = $body['pages'];
		} else {
			$this->apiController->invalidParameterError('pages');
		}

		try {
			foreach ($pages as $type => $page) {
				if (ServicePageType::isValidValue($type)) {
					$this->pageRepository->updateServicePage($levelId, $type, $page);
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

	public function getCommonPagesByLevel(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);

		return [
			SettingsKey::LOGIN_PAGE => $this->pageRepository->getCommonLoginPageId(),
			SettingsKey::DASHBOARD_PAGE => $this->pageRepository->getCommonDashboardPageId(),
			SettingsKey::TIME_LOCKED_PAGE => $this->pageRepository->getTimedUnlockNoAccessPageId(),
		];

	}

	public function updateCommonPagesForLevel(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$pages = [];

		if (isset($body['pages']) && is_array($body['pages'])) {
			$pages = $body['pages'];
		} else {
			$this->apiController->invalidParameterError('pages');
		}

		try {
			$settingsData = [];
			foreach ($pages as $type => $page) {
				if (SettingsKey::isValidValue($type)) {
					$settingsData[$type] = $page;
				}
			}
			$this->settingsRepository->updateSettings(new Settings($settingsData));
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Failed to update common pages",
			]);
		}

		$this->apiController->callbackSettingsSaved();
	}


}
