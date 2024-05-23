<?php

namespace FapiMember\Model\Enums;

use FapiMember\Library\SmartEmailing\Types\Enum;

class Alert extends Enum
{
	const API_FORM_EMPTY = 'apiFormEmpty';

	const API_FORM_SUCCESS ='apiFormSuccess';

	const API_FORM_ERROR = 'apiFormError';

	const API_FORM_CREDENTIALS_EXIST = 'apiFormCredentialsExist';

	const API_FORM_TOO_MANY_CREDENTIALS = 'apiFormTooManyCredentials';

	const API_FORM_CREDENTIALS_REMOVED = 'apiFormCredentialsRemoved';

	const SECTION_NAME_EMPTY = 'sectionNameEmpty';

	const LEVEL_NAME_OR_PARENT_EMPTY = 'levelNameOrParentEmpty';

	const SECTION_NOT_FOUND = 'sectionNotFound';

	const REMOVE_LEVEL_SUCCESSFUL = 'removeLevelSuccessful';

	const EDIT_LEVEL_SUCCESSFUL = 'editLevelSuccessful';

	const LEVEL_ID_OR_TO_ADD_EMPTY = 'levelIdOrToAddEmpty';

	const EDIT_LEVEL_NO_NAME = 'editLevelNoName';

	const EDIT_MAILS_REMOVED = 'editMailsRemoved';

	const EDIT_MAILS_UPDATED = 'editMailsUpdated';

	const EDIT_OTHER_PAGES_REMOVED = 'editOtherPagesRemoved';

	const EDIT_OTHER_PAGES_UPDATED = 'editOtherPagesUpdated';

	const SETTINGS_SETTINGS_UPDATED = 'settingsSettingsUpdated';

	const SETTINGS_SETTINGS_NO_VALID_PAGE = 'settingsSettingsNoValidPage';

}
