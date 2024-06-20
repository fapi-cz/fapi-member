<?php

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\Model\User;
use FapiMember\Repository\UserRepository;
use FapiMember\Library\SmartEmailing\Types\StringType;

class UserService
{
	private UserRepository $userRepository;

	public function __construct()
	{
		$this->userRepository = Container::get(UserRepository::class);
	}

	public function getOrCreateUser(array $userData, array &$props, $retryCount = 0): User|null
	{
		$email = $userData['email'];
		$user = $this->userRepository->getUserByEmail($email);

		if ($user === null) {
			$user = $this->userRepository->getUserByLogin($email);
		}

		if ($user instanceof User) {
			$props['user_id']  = $user->getId();
			$props['new_user'] = false;

			return $user;
		}

		$password = wp_generate_password(16);

		$userId = $this->userRepository->save(
			StringType::extractOrNull($userData, 'first_name'),
			StringType::extractOrNull($userData, 'last_name'),
			$email,
			$password,
		);

		$user = null;

		if ($userId !== null) {
			$user = $this->userRepository->getUserById($userId);
		}

		if ($user !== null) {
			$props['email'] = $user->getEmail();
			$props['login'] = $user->getEmail();
			$props['password'] = $password;
			$props['new_user'] = true;
			$props['user_id'] = $userId;
		} elseif ($retryCount < 5) {
			return $this->getOrCreateUser($email, $props, ++$retryCount);
		}

		return $user;
	}
}
