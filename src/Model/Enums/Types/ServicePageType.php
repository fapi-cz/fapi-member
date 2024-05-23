<?php

namespace FapiMember\Model\Enums\Types;

use FapiMember\Library\SmartEmailing\Types\Enum;

class ServicePageType extends Enum
{
	const NO_ACCESS = 'noAccess';

	const LOGIN = 'login';

	const AFTER_LOGIN = 'afterLogin';
}
