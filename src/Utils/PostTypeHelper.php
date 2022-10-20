<?php

namespace FapiMember\Utils;

class PostTypeHelper {

	public static $excluded_post_type_prefixes = array( 'jet-', 'elementor_', 'elemental_' );

	/**
	 * @param bool $cpt_only
	 * @return array<string>
	 */
	public static function getSupportedPostTypes( $cpt_only = false ) {
		$excluded_post_types          = $cpt_only ? array( 'attachment', 'page', 'post' ) : array( 'attachment' );
		$supported_post_types_objects = get_post_types( array( 'public' => true ), 'objects', 'and' );

		foreach ( $supported_post_types_objects as $obj ) {
			$name = $obj->name;

			if ( in_array( $name, $excluded_post_types, true ) ) {
				continue;
			}

			foreach ( self::$excluded_post_type_prefixes as $prefix ) {
				if ( strpos( $name, $prefix ) === 0 ) {
					continue 2;
				}
			}

			$supported_post_types[] = $name;
		}

		sort( $supported_post_types );

		return $supported_post_types;
	}

}
