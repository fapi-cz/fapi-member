<?php declare(strict_types=1);

namespace FapiMember\Repository;

use FapiMember\Container\Container;
use FapiMember\Model\User;
use WP_Error;
use WP_User;

class UserRepository extends Repository
{

	public function getCurrentUser(): User|null
	{
		if (!is_user_logged_in()) {
			return null;
		}

		return $this->toEntity(wp_get_current_user());
	}

	public function getUserById(int $id): User|null
	{
		 return $this->getUserBy('id', $id);
	}

	public function getUserByEmail(string $email): User|null
	{
		 return $this->getUserBy('email', $email);
	}

	public function getUserByLogin(string $login): User|null
	{
		 return $this->getUserBy('login', $login);
	}

	private function getUserBy(string $key, mixed $value): User|null
	{
		$user = get_user_by($key, $value);

		return $this->toEntity($user);
	}

	public function save(
		string|null $firstName,
		string|null $lastName,
		string $email,
		string $password
	): int|WP_Error
	{
		$userId = wp_insert_user([
			'user_pass' => $password,
			'user_login' => $email,
			'user_nicename' => str_replace('@', '_', $email),
			'user_email' => $email,
			'first_name' => $firstName,
			'last_name' => $lastName,
		]);

		if ($userId instanceof WP_Error) {
			return $userId;
		}

		$user = get_user_by('id', $userId);
		$user->set_role('member');

		return $userId;
	}

	/** @return array<User> */
	public function getAllUsers(): array
	{
		return $this->toEntities(get_users());
	}

	/**
	 * @param array<mixed> $users
	 * @return array<User>
	 */
	private function toEntities(array $users): array
	{
		$entities = [];
		foreach ($users as $user) {
			$entities[] = $this->toEntity($user);
		}

		return $entities;
	}

	private function toEntity(mixed $user): User|null
	{
		if (!$user instanceof WP_User) {
			return null;
		}

		return new User([
			'id' => $user->ID,
			'email' => $user->user_email,
			'login' => $user->user_login,
			'login_name' => $user->user_login,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'roles' => $user->roles,
		]);
	}

}
