<?php

namespace FapiMember;

use function define;
use function get_post_types;

/**
 * Plugin Name:       FAPI Member
 * Plugin URI:        https://fapi.cz/
 * Description:       Plugin FAPI pro jednoduchou správu členských sekcí na webu.
 * Version:           1.9.5
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

define('FAPI_MEMBER_PLUGIN_VERSION', '1.9.5');

$FapiPlugin = new FapiMemberPlugin();


/**
 * @param bool
 * @return array
 */
function get_supported_post_types($cpt_only = false) {
  $excluded_post_types = $cpt_only ? array('attachment', 'page', 'post') : array('attachment');
  $excluded_post_type_prefixes = array('jet-', 'elementor_', 'elemental_');

  $supported_post_types_objects = get_post_types(array('public' => true), 'objects', 'and');

  foreach($supported_post_types_objects as $obj) {
    $name = $obj->name;

    if (in_array($name, $excluded_post_types)) {
      continue;
    }

    $exclude = false;
    foreach($excluded_post_type_prefixes as $prefix) {
      if (strpos($name, $prefix) === 0) {
        $exclude = true;
        break;
      }
    }

    if (!$exclude) {
      $supported_post_types[] = $name;
    }
  }

  //var_dump($supported_post_types); die();
  sort($supported_post_types);

  return $supported_post_types;
}
