<?php

namespace FapiMember\Elementor\Filters;

if ( ! defined( 'ABSPATH' ) ) {
	return; // Exit if accessed directly.
}

use FapiMember\Utils\DisplayHelper;

final class ContentProtector {


	const ELEMENTS_TO_PROTECT = array( 'section', 'column', 'widget' );

	public static function register() {
		foreach ( self::ELEMENTS_TO_PROTECT as $key ) {
			add_filter(
				'elementor/frontend/' . $key . '/should_render',
				array( self::class, 'protect' ),
				10,
				2
			);
		}
	}

	public static function protect( $shouldRender, $element ) {
		if ( ! is_object( $element ) || ! method_exists( $element, 'get_settings' ) ) {
			return $shouldRender;
		}

		if ( ! isset( $element->get_settings()['hasSectionOrLevel'] ) ) {
			return $shouldRender;
		}

		if ( ! isset( $element->get_settings()['fapiSectionAndLevels'] ) ) {
			return $shouldRender;
		}

		return $shouldRender && DisplayHelper::shouldContentBeRendered(
			(string) $element->get_settings()['hasSectionOrLevel'],
			$element->get_settings()['fapiSectionAndLevels']
		);
	}
}
