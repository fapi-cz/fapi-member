<?php

namespace FapiMember\Model\Enums\Types;

use FapiMember\Library\SmartEmailing\Types\Enum;

class AlertType extends Enum
{
	const ERROR = 'error';

	const WARNING = 'warning';

	const SUCCESS = 'success';
}
