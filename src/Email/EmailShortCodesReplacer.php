<?php

namespace FapiMember\Email;

use function str_replace;

final class EmailShortCodesReplacer {


	/**
	 * @param string       $text
	 * @param array<mixed> $props
	 * @return string
	 */
	public static function replace( $text, $props ) {
		$map = array(
			'%%SEKCE%%'              => self::getSectionValue( $props ),
			'%%UROVEN%%'             => self::getLevelValue( $props ),
			'%%DNI%%'                => self::getDaysValue( $props ),
			'%%CLENSTVI_DO%%'        => self::getExpirationDateValue( $props ),
			'%%PRIHLASENI_ODKAZ%%'   => $props['login_link_url'],
			'%%PRIHLASOVACI_JMENO%%' => isset( $props['login'] ) ? $props['login'] : '',
			'%%HESLO%%'              => isset( $props['password'] ) ? $props['password'] : '',
		);

		foreach ( $map as $key => $value ) {
			$text = str_replace( $key, $value, $text );
		}

		return $text;
	}

	/**
	 * @param array<mixed>
	 * @return string
	 */
	private static function getSectionValue( array $props ) {
		if ( ( isset( $props['membership_level_added_is_section'] ) && $props['membership_level_added_is_section'] === false )
			|| ( isset( $props['membership_prolonged_is_section'] ) && $props['membership_prolonged_is_section'] === false )
		) {
			return '';
		}

		if ( isset( $props['membership_prolonged_level_name'] ) ) {
			return $props['membership_prolonged_level_name'];
		}

		if ( isset( $props['membership_level_added_level_name'] ) ) {
			return $props['membership_level_added_level_name'];
		}

		return '';
	}

	/**
	 * @param array<mixed> $props
	 * @return string
	 */
	private static function getLevelValue( array $props ) {
		if ( ( isset( $props['membership_level_added_is_section'] ) && $props['membership_level_added_is_section'] === true )
			|| ( isset( $props['membership_prolonged_is_section'] ) && $props['membership_prolonged_is_section'] === true )
		) {
			return '';
		}

		if ( isset( $props['membership_prolonged_level_name'] ) ) {
			return $props['membership_prolonged_level_name'];
		}

		if ( isset( $props['membership_level_added_level_name'] ) ) {
			return $props['membership_level_added_level_name'];
		}

		return '';
	}

	/**
	 * @param array<mixed> $props
	 * @return int|string
	 */
	private static function getDaysValue( array $props ) {
		if ( isset( $props['membership_prolonged_days'] ) ) {
			return $props['membership_prolonged_days'];
		}

		if ( isset( $props['membership_level_added_days'] ) ) {
			return $props['membership_level_added_days'];
		}

		if ( isset( $props['membership_prolonged_to_unlimited'] ) || isset( $props['membership_level_added_unlimited'] ) ) {
			return 'neomezeně';
		}

		return '';
	}

	/**
	 * @param array<mixed> $props
	 * @return string
	 */
	private static function getExpirationDateValue( array $props ) {
		if ( isset( $props['membership_prolonged_until'] ) ) {
			return $props['membership_prolonged_until']->format( 'j. n. Y' );
		}

		if ( isset( $props['membership_level_added_until'] ) ) {
			return $props['membership_level_added_until']->format( 'j. n. Y' );
		}

		if ( isset( $props['membership_prolonged_to_unlimited'] ) || isset( $props['membership_level_added_unlimited'] ) ) {
			return 'neomezené';
		}

		return '';
	}

}
