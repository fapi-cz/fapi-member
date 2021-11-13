<?php

namespace FapiMember;

use WP_User;

final class FapiUserUtils
{

	/**
	 * @param string $email
	 * @param array<mixed> $props
	 * @return int|false
	 */
	public function createUserIfNeeded($email, &$props)
	{
		$user = get_user_by('email', $email);

		if ($user !== false) {
			// is duplicate
			$props['user_id'] = $user->ID;
			$props['new_user'] = false;

			return false;
		}

		$password = wp_generate_password(16, true, false);

		$userData = [
			'user_pass' => $password,
			'user_login' => $email,
			'user_nicename' => str_replace('@', '_', $email),
			'user_email' => $email,
		];
		$userId = wp_insert_user($userData);

		$props['email'] = $email;
		$props['login'] = $email;
		$props['password'] = $password;
		$props['new_user'] = true;

		if (is_int($userId)) {
			$user = new WP_User($userId);
			$user->set_role('member');
			$props['user_id'] = $userId;
		}

		return $userId;
	}

}
