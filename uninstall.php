<?php

namespace FapiMemberOld;

// if uninstall.php is not called by WordPress, die
use FapiMember\Model\Enums\Keys\MetaKey;
use FapiMember\Model\Enums\Keys\OptionKey;

if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}

require __DIR__ . '/fapi-member.php';

$options = [
	OptionKey::API_CHECKED,
	OptionKey::SETTINGS,
	OptionKey::API_USER,
	OptionKey::API_KEY,
	OptionKey::API_CREDENTIALS,
];

foreach ($options as $o) {
	delete_option($o);
}

// unregistering taxonomy does not remote its terms and their meta
$terms = get_terms(['taxonomy' => 'fapi_levels', 'hide_empty' => false,]);
foreach ($terms as $term) {
	wp_delete_term($term->term_id, 'fapi_levels');
}
unregister_taxonomy('fapi_levels');

$users = get_users();
$metaKeys = [
	MetaKey::MEMBERSHIP,
	MetaKey::MEMBERSHIP_HISTORY,
];

foreach ($metaKeys as $k) {
	delete_metadata('user', 0, $k, '', true);
}
