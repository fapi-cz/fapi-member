<?php

namespace FapiMember;

use function define;

/**
 * Plugin Name:       FAPI Member
 * Plugin URI:        https://fapi.cz/
 * Description:       Plugin FAPI pro jednoduchou správu členských sekcí na webu.
 * Version:           1.9.9
 * Requires at least: 5.8
 * Requires PHP:      5.6
 * Author:            FAPI Business s.r.o.
 * Author URI:        https://fapi.cz/
 * License:           GPLv2 or later
 * Text Domain:       fapi-member
 * Domain Path:       /languages
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/multiple-blocks/multiple-blocks.php';
require __DIR__ . '/src/Elementor/fapi-member.php';

define('FAPI_MEMBER_PLUGIN_VERSION', '1.9.9');

$FapiPlugin = new FapiMemberPlugin();
