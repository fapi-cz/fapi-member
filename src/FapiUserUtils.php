<?php

namespace FapiMember;

use WP_Error;
use WP_User;
use function get_user_by;
use function is_int;
use function str_replace;

final class FapiUserUtils {


	/**
	 * @param string       $email
	 * @param array<mixed> $props
	 * @return WP_User|WP_Error
	 */
	public function getOrCreateUser( $email, &$props, $retryCount = 0 ) {
		$user = get_user_by( 'email', $email );

		if ( $user === false ) {
			$user = get_user_by( 'login', $email );
		}

		if ( $user !== false ) {
			$props['user_id']  = $user->ID;
			$props['new_user'] = false;

			return $user;
		}

		$password = wp_generate_password( 16, true, false );
		$userId   = wp_insert_user(
			array(
				'user_pass'     => $password,
				'user_login'    => $email,
				'user_nicename' => str_replace( '@', '_', $email ),
				'user_email'    => $email,
			)
		);

		if ( $retryCount < 5 && $userId instanceof WP_Error ) {
			return self::getOrCreateUser( $email, $props, ++$retryCount );
		}

		$props['email']    = $email;
		$props['login']    = $email;
		$props['password'] = $password;
		$props['new_user'] = true;

		if ( is_int( $userId ) ) {
			$user = new WP_User( $userId );
			$user->set_role( 'member' );
			$props['user_id'] = $userId;

			return $user;
		}

		return $userId;
	}

}
