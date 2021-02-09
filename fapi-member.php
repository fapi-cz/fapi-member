<?php
/**
 * Plugin Name:       Fapi Member
 * Plugin URI:        https://fapi.cz/
 * Description:       Fapi plugin for membership management and access control.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            FAPI
 * Author URI:        https://fapi.cz/
 * License:           proprietary
 * Text Domain:       fapi
 * Domain Path:       /languages
 */

include __DIR__ . '/src/FapiMemberPlugin.php';
include __DIR__ . '/src/FapiLevels.php';

$plugin = new FapiMemberPlugin();
$plugin->addHooks();
$fapiLevels = new FapiLevels();