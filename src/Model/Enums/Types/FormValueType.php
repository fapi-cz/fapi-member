<?php

namespace FapiMember\Model\Enums\Types;

use FapiMember\Library\SmartEmailing\Types\Enum;

class FormValueType extends Enum
{
	const VALID_LEVEL_ID = 'validLevelId';

	const VALID_LEVEL_IDS = 'validLevelIds';

	const VALID_PAGE_IDS = 'validPageIds';

	const VALID_PAGE_ID = 'validPageId';

	const ANY_STRING = 'anyString';

	const CHECKBOX = 'checkBox';

	const SINGLE_INT = 'singleInt';

	const INT_LIST = 'intList';

	const STR_LIST = 'strList';

	const VALID_EMAIL_TYPE = 'validEmailType';

	const VALID_SERVICE_PAGE_TYPE = 'validServicePageType';

	const VALID_DIRECTION = 'validDirection';

	const USER_PROFILE_LEVELS = 'userProfileLevels';

}
