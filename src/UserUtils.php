<?php


class UserUtils {
	public function createUser( $email, &$props ) {
		$user = get_user_by( 'email', $email );
		if ( $user !== false ) {
			// is duplicate
			$props['user_id']  = $user->ID;
			$props['new_user'] = false;

			return false;
		}
		$password = wp_generate_password( 16, true, false );
		$userData = [
			'user_pass'     => $password,
			'user_login'    => $email,
			'user_nicename' => str_replace( '@', '_', $email ),
			'user_email'    => $email,
			'user_role'     => 'subscriber'
		];
		$res      = wp_insert_user( $userData );

		$props['email']    = $email;
		$props['login']    = $email;
		$props['password'] = $password;
		$props['new_user'] = true;
		if ( is_int( $res ) ) {
			$props['user_id'] = $res;
		}

		return $res;
	}
}