<?php

namespace FapiMember\Model\Enums\Keys;

use FapiMember\Library\SmartEmailing\Types\Enum;

class OptionKey extends Enum
{
	const SETTINGS = 'fapiSettings';

	const FAPI_MEMBER_VERSION = 'fapiMemberVersion';

	const API_USER = 'fapiMemberApiEmail';

	const API_KEY = 'fapiMemberApiKey';

	const API_CREDENTIALS = 'fapiMemberApiCredentials';

	const API_URL = 'fapiMemberApiUrl';

	const API_CHECKED = 'fapiMemberApiChecked';

	const TOKEN = 'fapiMemberApiToken';

	const IS_DEVELOPMENT = 'fapiIsDevelopment';

	const POST_TYPES = 'fapi_member_post_types';

	const LEVELS_ORDER = 'fapi_levels_order_new';

}
