<?php

namespace FapiMember\Api\V2\Endpoints;

use FapiMember\Api\V2\ApiController;
use FapiMember\Container\Container;
use FapiMember\Library\Nette\Utils\Arrays;
use FapiMember\Library\SmartEmailing\Types\BoolType;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Library\SmartEmailing\Types\StringType;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Repository\MembershipChangeRepository;
use FapiMember\Service\ApiService;
use FapiMember\Service\StatisticsService;
use FapiMember\Utils\DateTimeHelper;
use WP_REST_Request;

class StatisticsController
{
	private MembershipChangeRepository $membershipChangeRepository;
	private StatisticsService $statisticsService;
	private ApiController $apiController;

	public function __construct()
	{
		$this->membershipChangeRepository = Container::get(MembershipChangeRepository::class);
		$this->statisticsService = Container::get(StatisticsService::class);
		$this->apiService = Container::get(ApiService::class);
		$this->apiController = Container::get(ApiController::class);
	}

	public function getMembershipChangesForUser(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$userId = $this->apiController->extractParam($body, 'user_id', IntType::class);

		$changes = $this->membershipChangeRepository->getForUser($userId);
		$changeData = [];

		foreach ($changes as $change) {
			$changeData[] = $change->toJson();
		}

		return $changeData;
	}

	public function getMemberCountsForPeriod(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$dateFrom = $this->apiController->extractParam($body, 'date_from', StringType::class);
		$dateTo = $this->apiController->extractParam($body, 'date_to', StringType::class);
		$groupLevels = $this->apiController->extractParam($body, 'group_levels', BoolType::class);
		$levelIds = [];

		foreach ($body['level_ids'] as $levelId) {
			$levelIds[] = (int) $levelId;
		}

		$dateFrom = DateTimeHelper::createOrNull($dateFrom, Format::DATE);
		$dateTo = DateTimeHelper::createOrNull($dateTo, Format::DATE);

		return $this->statisticsService->getMemberCountsForPeriod($dateFrom, $dateTo, $levelIds, $groupLevels);
	}

	public function getMemberCountChangesForPeriod(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$dateFrom = $this->apiController->extractParam($body, 'date_from', StringType::class);
		$dateTo = $this->apiController->extractParam($body, 'date_to', StringType::class);
		$groupLevels = $this->apiController->extractParam($body, 'group_levels', BoolType::class);
		$levelIds = [];

		foreach ($body['level_ids'] as $levelId) {
			$levelIds[] = (int) $levelId;
		}

		$dateFrom = DateTimeHelper::createOrNull($dateFrom, Format::DATE);
		$dateTo = DateTimeHelper::createOrNull($dateTo, Format::DATE);

		return $this->statisticsService->getMemberCountChangesForPeriod($dateFrom, $dateTo, $levelIds, $groupLevels);
	}

	public function getChurnRate(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$dateFrom = $this->apiController->extractParam($body, 'date_from', StringType::class);
		$dateTo = $this->apiController->extractParam($body, 'date_to', StringType::class);
		$groupLevels = $this->apiController->extractParam($body, 'group_levels', BoolType::class);
		$levelIds = [];

		foreach ($body['level_ids'] as $levelId) {
			$levelIds[] = (int) $levelId;
		}

		$dateFrom = DateTimeHelper::createOrNull($dateFrom, Format::DATE);
		$dateTo = DateTimeHelper::createOrNull($dateTo, Format::DATE);

		return $this->statisticsService->getAcquisitionOrChurnRate($dateFrom, $dateTo, $levelIds, $groupLevels, true);
	}

	public function getAcquisitionRate(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$dateFrom = $this->apiController->extractParam($body, 'date_from', StringType::class);
		$dateTo = $this->apiController->extractParam($body, 'date_to', StringType::class);
		$groupLevels = $this->apiController->extractParam($body, 'group_levels', BoolType::class);
		$levelIds = [];

		foreach ($body['level_ids'] as $levelId) {
			$levelIds[] = (int) $levelId;
		}

		$dateFrom = DateTimeHelper::createOrNull($dateFrom, Format::DATE);
		$dateTo = DateTimeHelper::createOrNull($dateTo, Format::DATE);

		return $this->statisticsService->getAcquisitionOrChurnRate($dateFrom, $dateTo, $levelIds, $groupLevels, false);
	}

	public function getActiveCountsForPeriod(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$dateFrom = $this->apiController->extractParam($body, 'date_from', StringType::class);
		$dateTo = $this->apiController->extractParam($body, 'date_to', StringType::class);
		$groupLevels = $this->apiController->extractParam($body, 'group_levels', BoolType::class);
		$levelIds = [];

		foreach ($body['level_ids'] as $levelId) {
			$levelIds[] = (int) $levelId;
		}

		$dateFrom = DateTimeHelper::createOrNull($dateFrom, Format::DATE);
		$dateTo = DateTimeHelper::createOrNull($dateTo, Format::DATE);

		return $this->statisticsService->getActiveCountsForPeriod($dateFrom, $dateTo);
	}
}
