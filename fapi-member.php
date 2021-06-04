<?php
/**
 * Plugin Name:       FAPI Member
 * Plugin URI:        https://fapi.cz/
 * Description:       Plugin FAPI pro jednoduchou správu členských sekcí na webu.
 * Version:           1.6.1
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            FAPI Business s.r.o.
 * Author URI:        https://fapi.cz/
 * License:           GPLv2 or later
 * Text Domain:       fapi-member
 * Domain Path:       /languages
 */

include __DIR__ . '/src/FapiTermEnvelope.php';
include __DIR__ . '/src/FapiMemberTools.php';
include __DIR__ . '/src/FapiMemberPlugin.php';
include __DIR__ . '/src/FapiLevels.php';
include __DIR__ . '/src/FapiUserUtils.php';
include __DIR__ . '/src/FapiMembership.php';
include __DIR__ . '/src/FapiMembershipLoader.php';
include __DIR__ . '/src/FapiApi.php';
include __DIR__ . '/src/FapiSanitization.php';

$FapiPlugin = new FapiMemberPlugin();