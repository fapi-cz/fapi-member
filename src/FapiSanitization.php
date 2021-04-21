<?php


class FapiSanitization {

	const POST = 'POST';
	const GET = 'GET';

	const VALID_LEVEL_ID = 'validLevelId';
	const VALID_PAGE_IDS = 'validPageIds';
	const VALID_PAGE_ID = 'validPageId';
	const ANY_STRING = 'anyString';
	const VALID_EMAIL_TYPE = 'validEmailType';
	const VALID_OTHER_PAGE_TYPE = 'validOtherPageType';
	const VALID_DIRECTION = 'validDirection';

	public $fapiLevels;

	/**
	 * @param FapiLevels $fapiLevels
	 */
	public function __construct( $fapiLevels ) {
		$this->fapiLevels = $fapiLevels;
	}


	public function loadPostValue( $key, $sanitizer, $default = null ) {
		return self::loadFormValue( self::POST, $key, $sanitizer, $default );
	}

	public function loadGetValue( $key, $sanitizer, $default = null ) {
		return self::loadFormValue( self::GET, $key, $sanitizer, $default );
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
				throw new \RuntimeException( 'Not implemented method.' );
		}

		$raw = ( isset( $arr[ $key ] ) ) ? $arr[ $key ] : $default;
		if ( $raw === null ) {
			// input is missing
			return null;
		}
		if ( ! is_callable( $sanitizer ) ) {
			throw new \RuntimeException( 'Sanitizer should be callable.' );
		}

		return $sanitizer( $raw, $default );
	}

	public function validLevelId( $input, $default ) {
		$levelIds = $this->fapiLevels->allIds();
		if ( in_array( (int) $input, $levelIds ) ) {
			return (int) $input;
		}

		return $default;
	}

	public function validPageIds( $input, $default ) {
		if ( ! is_array( $input ) ) {
			new \RuntimeException( 'This sanitizer only accepts array.' );
		}
		$pages   = get_posts( [ 'post_type' => 'page', 'post_status' => [ 'publish' ], 'numberposts' => - 1 ] );
		$pageIds = array_reduce( $pages,
			function ( $carry, $one ) {
				/** @var WP_Post $one */
				$carry[] = (int) $one->ID;

				return $carry;
			},
			                     [] );
		$valid   = array_filter( $input,
			function ( $one ) use ( $pageIds ) {
				return in_array( (int) $one, $pageIds );
			} );

		return array_map( 'intval', $input );
	}

	public function anyString( $input, $default ) {
		if ( strlen( (string) $input ) < 1 ) {
			return $default;
		}

		return (string) $input;
	}

	public function validEmailType( $input, $default ) {
		if ( in_array( $input, FapiLevels::$emailTypes ) ) {
			return $input;
		}

		return $default;
	}

	public function validOtherPageType( $input, $default ) {
		if ( in_array( $input, FapiLevels::$pageTypes ) ) {
			return $input;
		}

		return $default;
	}

    public function validDirection( $input, $default ) {
        if ( in_array( $input, ['up', 'down'] ) ) {
            return $input;
        }

        return $default;
    }

	public function validPageId( $input, $default ) {
		$pages = get_posts( [ 'post_type'   => 'page',
		                      'post_status' => [ 'publish' ],
		                      'numberposts' => - 1,
		                      'include'     => [ $input ]
		                    ] );
		if ( count( $pages ) > 0 ) {
			return (int) $input;
		}

		return $default;
	}

}