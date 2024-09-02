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

	const REMOVE_LEVEL_SUCCESSFUL = 'removeLevelSuccessful';

	const INTERNAL_ERROR = 'internalError';

	const SETTINGS_SAVED = 'settingsSaved';

	const LEVEL_ALREADY_EXISTS = 'levelAlreadyExists';

	const REORDER_FAILED = 'reorderFailed';

	const MEMBERSHIP_REGISTERED_EXTENDED = 'membershipRegisteredExtended';

	const MEMBERSHIP_UNTIL_EXTENDED = 'membershipUntilExtended';

	const INVALID_EMAIL = 'invalidEmail';

	const IMPORT_FAILED = 'importFailed';

	const IMPORT_LEVEL_ID_DOESNT_EXIST = 'importLevelIdDoesntExist';

}
