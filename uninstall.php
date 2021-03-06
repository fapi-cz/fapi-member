<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

include ('src/FapiMemberPlugin.php');
include ('src/FapiLevels.php');
include ('src/FapiMembershipLoader.php');


$options = [
    FapiMemberPlugin::OPTION_KEY_API_CHECKED,
    FapiMemberPlugin::OPTION_KEY_SETTINGS,
    FapiMemberPlugin::OPTION_KEY_API_USER,
    FapiMemberPlugin::OPTION_KEY_API_KEY,
];

foreach ($options as $o) {
    delete_option($o);
}

unregister_taxonomy(FapiLevels::TAXONOMY);

$users = get_users();
$metaKeys = [
    FapiMembershipLoader::MEMBERSHIP_META_KEY,
    FapiMembershipLoader::MEMBERSHIP_HISTORY_META_KEY
];

foreach ($metaKeys as $k) {
    delete_metadata('user', 0, $k, '', true);
}
    
