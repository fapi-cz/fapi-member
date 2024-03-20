<?php

/**
 * Plugin Name: FAPI Member - Elementor extended
 * Description: FAPI Member plugin fot elementor.
 * Plugin URI:  https://fapi.cz/
 * Version:     1.0.0
 * Author:      FAPI Business s.r.o.
 * Author URI:  https://fapi.cz/
 * Text Domain: fapi-member
 *
 * Elementor tested up to: 3.5.0
 * Elementor Pro tested up to: 3.5.0
 */

use FapiMember\Elementor\Category\CategoryRegister;
use FapiMember\Elementor\Filters\ContentProtector;
use FapiMember\Elementor\Widgets\FapiFormWidget;
use FapiMember\Elementor\Widgets\FapiUnlockButton;
use FapiMember\Elementor\WidgetSettingsExtender\WidgetsSettingsExtender;

if ( ! defined( 'ABSPATH' ) ) {
	return; // Exit if accessed directly.
}

add_action(
	'elementor/widgets/register',
	function ( $widgetsManager ) {
		$widgetsManager->register( new FapiFormWidget() );
		$widgetsManager->register( new FapiUnlockButton() );
	}
);
CategoryRegister::register();
WidgetsSettingsExtender::register();
ContentProtector::register();
