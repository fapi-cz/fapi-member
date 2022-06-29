<?php

namespace FapiMember;

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}

require __DIR__ . '/fapi-member.php';

$options = [
	FapiMemberPlugin::OPTION_KEY_API_CHECKED,
	FapiMemberPlugin::OPTION_KEY_SETTINGS,
	FapiMemberPlugin::OPTION_KEY_API_USER,
	FapiMemberPlugin::OPTION_KEY_API_KEY,
	FapiMemberPlugin::OPTION_KEY_API_CREDENTIALS
];

foreach ($options as $o) {
	delete_option($o);
}

// unregistering taxonomy does not remote its terms and their meta
$terms = get_terms(['taxonomy' => FapiLevels::TAXONOMY, 'hide_empty' => false,]);
foreach ($terms as $term) {
	wp_delete_term($term->term_id, FapiLevels::TAXONOMY);
}
unregister_taxonomy(FapiLevels::TAXONOMY);

$users = get_users();
$metaKeys = [
	FapiMembershipLoader::MEMBERSHIP_META_KEY,
	FapiMembershipLoader::MEMBERSHIP_HISTORY_META_KEY,
];

foreach ($metaKeys as $k) {
	delete_metadata('user', 0, $k, '', true);
}
