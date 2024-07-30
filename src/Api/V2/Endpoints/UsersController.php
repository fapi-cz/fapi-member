<?php

namespace FapiMember\Api\V2\Endpoints;

use FapiMember\Api\V2\ApiController;
use FapiMember\Container\Container;
use FapiMember\Library\SmartEmailing\Types\IntType;
use FapiMember\Model\Enums\Types\RequestMethodType;
use FapiMember\Repository\MembershipRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Service\ApiService;
use WP_REST_Request;

class UsersController
{
	private UserRepository $userRepository;
	private MembershipRepository $membershipRepository;
	private ApiService $apiService;
	private ApiController $apiController;

	public function __construct()
	{
		$this->userRepository = Container::get(UserRepository::class);
		$this->membershipRepository = Container::get(MembershipRepository::class);
		$this->apiService = Container::get(ApiService::class);
		$this->apiController = Container::get(ApiController::class);
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
