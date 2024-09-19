<?php

namespace FapiMember\Model\Enums\Types;

use FapiMember\Library\SmartEmailing\Types\Enum;

class MembershipChangeType extends Enum
{
	const CREATED = 'created';

	const UPDATED = 'updated';

	const EXTENDED = 'extended';

	const EXPIRED = 'expired';

	const DELETED = 'deleted';
}
