<?php
/**
 * Plugin Name:       FAPI Member
 * Description:       FAPI plugin for membership management and access control.
 * Version:           1.2.0
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            FAPI
 * Author URI:        https://fapi.cz/
 * License:           GPLv2 or later
 * Text Domain:       fapi
 * Domain Path:       /languages
 */

include __DIR__ . '/src/FapiMemberPlugin.php';
include __DIR__ . '/src/FapiLevels.php';
include __DIR__ . '/src/FapiUserUtils.php';
include __DIR__ . '/src/FapiMembership.php';
include __DIR__ . '/src/FapiMembershipLoader.php';
include __DIR__ . '/src/FapiApi.php';
include __DIR__ . '/src/FapiSanitization.php';

$FapiPlugin = new FapiMemberPlugin();