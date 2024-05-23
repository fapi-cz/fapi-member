<?php

namespace FapiMember\Model\Enums;

use FapiMember\Library\SmartEmailing\Types\Enum;

class SubPage extends Enum
{
	const SECTION_NEW = 'settingsSectionNew';

	const LEVEL_NEW = 'settingsLevelNew';

	const CONNECTION = 'connection';

	const SETTINGS_CONTENT_ADD = 'settingsContentAdd';

	const SETTINGS_CONTENT_REMOVE = 'settingsContentRemove';

	const SETTINGS_EMAILS = 'settingsEmails';

	const SETTINGS_PAGES = 'settingsPages';

	const SETTINGS_SETTINGS = 'settingsSettings';

	const SETTINGS_UNLOCKING = 'settingsUnlocking';
}
