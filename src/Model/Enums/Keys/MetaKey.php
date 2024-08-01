<?php

namespace FapiMember\Model\Enums\Keys;

use FapiMember\Library\SmartEmailing\Types\Enum;

class MetaKey extends Enum
{
	const DAYS_TO_UNLOCK = 'fapi_days_to_unlock';

	const DATE_UNLOCK = 'fapi_date_unlock';

	const AFTER_DATE_UNLOCK = 'fapi_after_date_unlock';

	const HOUR_UNLOCK = 'fapi_hour_unlock';

	const TIME_UNLOCK = 'fapi_time_unlock';

	const BUTTON_UNLOCK = 'fapi_button_unlock';

	const MEMBERSHIP = 'fapi_user_memberships';

	const MEMBERSHIP_HISTORY = 'fapi_user_memberships_history';

	const PAGES = 'fapi_pages';

	const NO_ACCESS_PAGE = 'fapi_page_noAccess';

	const LOGIN_PAGE = 'fapi_page_login';

	const AFTER_LOGIN_PAGE = 'fapi_page_afterLogin';

	const EMAIL_AFTER_REGISTRATION = 'fapi_email_afterRegistration';

	const EMAIL_AFTER_MEMBERSHIP_PROLONGED = 'fapi_email_afterMembershipProlonged';

	const EMAIL_AFTER_ADDING = 'fapi_email_afterAdding';

}
