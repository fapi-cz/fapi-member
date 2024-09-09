<?php

namespace FapiMember\Service;

use Exception;
use FapiMember\Container\Container;
use FapiMember\Model\User;
use FapiMember\Repository\UserRepository;
use FapiMember\Library\SmartEmailing\Types\StringType;
use WP_Error;

class UserService
{
	private UserRepository $userRepository;

	public function __construct()
	{
		$this->userRepository = Container::get(UserRepository::class);
	}

	public function getUser(string|null $email, array &$props = []): User|null
	{
		$user = $this->userRepository->getUserByEmail($email);

		if ($user === null) {
			$user = $this->userRepository->getUserByLogin($email);
		}

		if ($user instanceof User) {
			$props['new_user'] = false;

			return $user;
		}

		return null;
	}

	/**
	 * @throws Exception
	 */
	public function getOrCreateUser(array $userData, array &$props = [], $retryCount = 0): User|null
	{
		$email = $userData['email'];

		$user = $this->getUser($email, $props);

		if ($user !== null) {
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

		if (!$userId instanceof WP_Error) {
			$user = $this->userRepository->getUserById($userId);
		}

		if ($user !== null) {
			$props['password'] = $password;
			$props['new_user'] = true;
		} elseif ($retryCount < 5) {
			return $this->getOrCreateUser($userData, $props, ++$retryCount);
		} elseif ($userId instanceof WP_Error) {
			throw new Exception($userId->get_error_message(), 500);
		}

		return $user;
	}
}
