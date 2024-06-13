<?php

namespace FapiMember\Api\V2\Endpoints;

use FapiMember\Api\V2\ApiController;
use FapiMember\Container\Container;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Model\Membership;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\MembershipRepository;
use FapiMember\Repository\PageRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Service\MembershipService;
use FapiMember\Service\RedirectService;
use FapiMember\Utils\DateTimeHelper;
use Throwable;
use WP_REST_Request;

class MembershipsController
{
	private MembershipRepository $membershipRepository;
	private UserRepository $userRepository;
	private LevelRepository $levelRepository;
	private PageRepository $pageRepository;
	private MembershipService $membershipService;
	private RedirectService $redirectService;
	private ApiController $apiController;

	public function __construct()
	{
		$this->membershipRepository = Container::get(MembershipRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->pageRepository = Container::get(PageRepository::class);
		$this->membershipService = Container::get(MembershipService::class);
		$this->redirectService = Container::get(RedirectService::class);
		$this->apiController = Container::get(ApiController::class);
	}

	public function list(): array
	{
		$membershipsByUsers = $this->membershipRepository->getAll();
		$membershipData = [];

		foreach ($membershipsByUsers as $membershipsByUser) {
			foreach ($membershipsByUser as $membership) {
				$membershipData[] = $membership->toArray();
			}
		}

		return $membershipData;
	}

	public function getAllForUser(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$userId = $this->apiController->extractParam($body, 'user_id', IntType::class);

		$memberships = $this->membershipRepository->getActiveByUserId($userId);

		$membershipData = [];

		foreach ($memberships as $membership) {
			$membershipData[] = $membership->toArray();
		}

		return $membershipData;
	}

	public function updateAllForUser(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$userId = $this->apiController->extractParam($body, 'user_id', IntType::class);

		if (isset($body['memberships']) && is_array($body['memberships'])) {
			$membershipsData = $body['memberships'];
		} else {
			$this->apiController->invalidParameterError('memberships');
		}

		$memberships = [];

		foreach ($membershipsData as $membershipData) {
			$membershipData['user_id'] = $userId;
			$memberships[] = new Membership($membershipData);
		}

		try {
			$this->membershipService->saveAll($userId, $memberships);
		} catch (Throwable) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Failed to save memberships.",
			]);
		}

		$this->apiController->callbackSuccess();
	}

	public function unlockLevelForLoggedInUser(WP_REST_Request $request): void
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::GET);
		$params = $request->get_params();

		$levelId = $this->apiController->extractParamOrNull($params, 'level_id', IntType::class);
		$pageId = $this->apiController->extractParamOrNull($params, 'page_id', IntType::class);
		$userId = $this->apiController->extractParamOrNull($params, 'user_id', IntType::class);

		if ($levelId === null) {
			$this->redirectService->redirectToNoAccessPage($levelId);
		}

		$level = $this->levelRepository->getLevelById($levelId);

		if ($level === null || $userId === null || !$this->levelRepository->isButtonUnlock($levelId)) {
			$this->redirectService->redirectToNoAccessPage($levelId);
		}

		$memberships = $this->membershipRepository->getActiveByUserId($userId);
		$ownsParent = false;

		foreach ($memberships as $membership) {
			if ($membership->getLevelId() === $level->getParentId()){
				$ownsParent = true;
				break;
			}
		}

		if ($ownsParent === false) {
			$this->redirectService->redirectToNoAccessPage($levelId);
		}

		$this->membershipService->saveOne(new Membership([
			'level_id' => $levelId,
			'user_id' => $userId,
			'registered' => DateTimeHelper::getNow()->format(Format::DATE_TIME),
			'is_unlimited' => true,
		]));

		if ($pageId === null) {
			$pageId = $level->getAfterLoginPageId()
				?? $this->pageRepository->getCommonDashboardPageId();

			if ($pageId === null) {
				wp_redirect(home_url());
			}
		}

		wp_redirect(home_url() . '/?page_id=' . $pageId);
	}

}
