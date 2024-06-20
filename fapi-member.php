<?php

namespace FapiMember;

use FapiMember\Container\Container;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\MembershipRepository;
use FapiMember\Repository\SettingsRepository;
use FapiMember\Service\ApiService;
use FapiMember\Utils\TemplateProvider;
use function define;

/**
 * Plugin Name:       FAPI Member
 * Plugin URI:        https://fapi.cz/
 * Description:       Plugin FAPI pro jednoduchou správu členských sekcí na webu.
 * Version:           2.0.02
 * Requires at least: 5.9
 * Requires PHP:      8.1
 * Author:            FAPI Business s.r.o.
 * Author URI:        https://fapi.cz/
 * License:           GPLv2 or later
 * Text Domain:       fapi-member
 * Domain Path:       /languages
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/multiple-blocks/multiple-blocks.php';
require __DIR__ . '/src/Elementor/fapi-member.php';
require __DIR__ . '/src/Utils/functions.php';

define('FAPI_MEMBER_PLUGIN_VERSION', '2.0.02');

$FapiPlugin = new FapiMemberPlugin();

/** @var TemplateProvider $templateProvider */
$templateProvider = Container::get(TemplateProvider::class);

/** @var SettingsRepository $settingsRepository */
$settingsRepository = Container::get(SettingsRepository::class);

/** @var LevelRepository $levelRepository */
$levelRepository = Container::get(LevelRepository::class);

/** @var MembershipRepository $membershipRepository */
$membershipRepository = Container::get(MembershipRepository::class);

/** @var ApiService $apiService */
$apiService = Container::get(ApiService::class);
