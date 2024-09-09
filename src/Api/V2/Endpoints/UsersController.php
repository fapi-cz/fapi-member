<?php

namespace FapiMember\Api\V2\Endpoints;

use FapiMember\Api\V2\ApiController;
use FapiMember\Container\Container;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Library\SmartEmailing\Types\StringType;
use FapiMember\Model\Enums\Alert;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Repository\MembershipRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Service\ApiService;
use FapiMember\Service\UserService;
use WP_REST_Request;

class UsersController
{
	private UserRepository $userRepository;
	private MembershipRepository $membershipRepository;
	private UserService $userService;
	private ApiService $apiService;
	private ApiController $apiController;

	public function __construct()
	{
		$this->userRepository = Container::get(UserRepository::class);
		$this->membershipRepository = Container::get(MembershipRepository::class);
		$this->userService = Container::get(UserService::class);
		$this->apiService = Container::get(ApiService::class);
		$this->apiController = Container::get(ApiController::class);
	}

	public function list(): array
	{
		$users = $this->userRepository->getAllUsers();
		$userData = [];

		foreach ($users as $user) {
			$userData[] = $user->toArray();
		}
		return $userData;
	}

	public function getByEmail(WP_REST_Request $request): array|null
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$email = $this->apiController->extractParamOrNull($body, 'email', StringType::class);

		return $this->userService->getUser($email)?->toArray();
	}

	public function create(array $body): bool
	{
		$email = $this->apiController->extractParamOrNull($body, 'email', StringType::class);

		if ($email === null) {
			$this->apiController->callbackError([
				'class'=> self::class,
				'description' => "Missing paramater: email.",
			], Alert::MISSING_EMAIL);
		}

		if (!is_email($email)) {
			$this->apiController->callbackError([
				'class' => self::class,
				'description' => 'Invalid email provided. Email given: ' . $email,
			], Alert::INVALID_EMAIL);
		}

		$user = $this->userService->getOrCreateUser($body);

		return $user !== null;
	}

	public function listMembers(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::GET);
		$users = $this->userRepository->getAllMemberUsers();
		$usersData = [];

		foreach ($users as $user) {
			$memberships = $this->membershipRepository->getActiveByUserId($user->getId());
			$levelIds = [];

			foreach ($memberships as $membership) {
				$levelIds[] = $membership->getLevelId();
			}

			$user->setLevelIds($levelIds);

			$usersData[] = $user->toArray();
		}

		return $usersData;
	}

	public function getByLevel(WP_REST_Request $request): array
	{
		$this->apiController->checkRequestMethod($request, RequestMethodType::POST);
		$body = json_decode($request->get_body(), true);

		$levelId = $this->apiController->extractParam($body, 'level_id', IntType::class);

		$memberships = $this->membershipRepository->getAllByLevelId($levelId);
		$users = $this->userRepository->getAllUsers();
		$userData = [];

		foreach ($memberships as $membership) {
			foreach ($users as $user) {
				if ($user->getId() === $membership->getUserId()) {
					$userData[] = $user->toArray();
				}
			}
		}

		return $userData;
	}

}
