<?php

namespace FapiMember;

use FapiMember\Utils\PostTypeHelper;
use RuntimeException;
use WP_Post;
use function in_array;
use function is_numeric;

final class FapiSanitization {


	const POST = 'POST';
	const GET  = 'GET';

	const VALID_LEVEL_ID        = 'validLevelId';
	const VALID_PAGE_IDS        = 'validPageIds';
	const VALID_PAGE_ID         = 'validPageId';
	const ANY_STRING            = 'anyString';
	const CHECKBOX       		= 'checkBox';
	const SINGLE_INT 			= 'singleInt';
	const INT_LIST              = 'intList';
	const STR_LIST              = 'strList';
	const VALID_EMAIL_TYPE      = 'validEmailType';
	const VALID_OTHER_PAGE_TYPE = 'validOtherPageType';
	const VALID_DIRECTION       = 'validDirection';
	const DATE       			= 'date';

	public $fapiLevels;

	/**
	 * @param FapiLevels $fapiLevels
	 */
	public function __construct( $fapiLevels ) {
		$this->fapiLevels = $fapiLevels;
	}

	public function loadPostValue( $key, $sanitizer, $default = null ) {
		return $this->loadFormValue( self::POST, $key, $sanitizer, $default );
	}

	public function loadFormValue( $method, $key, $sanitizer, $default = null ) {
		switch ( $method ) {
			case self::GET:
				$arr = $_GET;
				break;
			case self::POST:
				$arr = $_POST;
				break;
			default:
				throw new RuntimeException( 'Not implemented method.' );
		}

		$raw = ( isset( $arr[ $key ] ) ) ? $arr[ $key ] : $default;

		if ( $raw === null && $sanitizer[1] !== 'checkBox') {
			// input is missing
			return null;
		}

		if ( ! is_callable( $sanitizer ) ) {
			throw new RuntimeException( 'Sanitizer should be callable.' );
		}

		return $sanitizer( $raw, $default );
	}

	public function loadGetValue( $key, $sanitizer, $default = null ) {
		return $this->loadFormValue( self::GET, $key, $sanitizer, $default );
	}

	public function validLevelId( $input, $default ) {
		$levelIds = $this->fapiLevels->allIds();
		if ( in_array( (int) $input, $levelIds, true ) ) {
			return (int) $input;
		}

		return $default;
	}

	/**
	 * @param array<int> $input
	 * @return array<int>
	 */
	public function validLevelIds( array $input ) {
		$levelIds = $this->fapiLevels->allIds();
		$out      = array();

		foreach ( $levelIds as $levelId ) {
			if ( ! in_array( $levelId, $input, true ) ) {
				continue;
			}

			$out[] = $levelId;
		}

		return $out;
	}

	public function validPageIds( $input, $default ) {
		if ( ! is_array( $input ) ) {
			throw new RuntimeException( 'This sanitizer only accepts array.' );
		}

		$pages   = get_posts(
			array(
				'post_type'   => PostTypeHelper::getSupportedPostTypes(),
				'post_status' => array( 'publish' ),
				'numberposts' => -1,
			)
		);
		$pageIds = array_reduce(
			$pages,
			static function ( $carry, $one ) {
				/**
				 * @var WP_Post $one
				 */
				$carry[] = (int) $one->ID;

				return $carry;
			},
			array()
		);

		$valid = array_filter(
			$input,
			static function ( $one ) use ( $pageIds ) {
				return in_array( (int) $one, $pageIds, true );
			}
		);

		return array_map( 'intval', $input );
	}

	public function anyString( $input, $default ) {
		if ( (string) $input === '' ) {
			return $default;
		}

		return (string) $input;
	}

	public function singleInt( $input, $default ) {
		if ( is_numeric( $input ) ) {
			return intval( $input );
		}

		return $default;
	}

	public function checkBox($input) {
		if ($input === 'on') {
			return true;
		}

		return false;
	}

	/**
	 * @param array<mixed> $input
	 * @return array<int>
	 */
	public function intList( array $input ) {
		$out = array();

		foreach ( $input as $key => $value ) {
			if ( ! is_numeric( $value ) ) {
				continue;
			}

			$out[ $key ] = (int) $value;
		}

		return $out;
	}

	/**
	 * @param array<mixed> $input
	 * @return array<string>
	 */
	public function strList( array $input ) {
		$out = array();

		foreach ( $input as $key => $value ) {
			if ( ! is_string( $value ) ) {
				continue;
			}

			$out[ $key ] = (string) $value;
		}

		return $out;
	}


	public function validEmailType( $input, $default ) {
		if ( in_array( $input, FapiLevels::$emailTypes, true ) ) {
			return $input;
		}

		return $default;
	}

	public function validOtherPageType( $input, $default ) {
		if ( in_array( $input, FapiLevels::$pageTypes, true ) ) {
			return $input;
		}

		return $default;
	}

	/**
	 * @param string $input
	 * @param string $default
	 * @return string
	 */
	public function validDirection( $input, $default ) {
		if ( in_array( $input, array( 'up', 'down' ) ) ) {
			return $input;
		}

		return $default;
	}

	public function validPageId( $input, $default ) {
		$pages = get_posts(
			array(
				'post_type'   => PostTypeHelper::getSupportedPostTypes(),
				'post_status' => array( 'publish' ),
				'numberposts' => -1,
				'include'     => array( $input ),
			)
		);

		if ( count( $pages ) > 0 ) {
			return (int) $input;
		}

		return $default;
	}

}
