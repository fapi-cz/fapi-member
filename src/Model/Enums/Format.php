<?php

namespace FapiMember\Model\Enums;

use FapiMember\Library\SmartEmailing\Types\Enum;

class Format extends Enum
{
	const DATE_TIME = 'Y-m-d\TH:i:s';

	const DATE_TIME_MINUTES = 'Y-m-d\TH:i';

	const DATE = 'Y-m-d';

	const DATE_TIME_BASIC = 'Y-m-d H:i:s';

}
