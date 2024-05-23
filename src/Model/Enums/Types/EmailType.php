<?php

namespace FapiMember\Model\Enums\Types;

use FapiMember\Library\SmartEmailing\Types\Enum;

class EmailType extends Enum
{
	const AFTER_REGISTRATION = 'afterRegistration';

	const AFTER_MEMBERSHIP_PROLONGED = 'afterMembershipProlonged';

	const AFTER_ADDING = 'afterAdding';

}
