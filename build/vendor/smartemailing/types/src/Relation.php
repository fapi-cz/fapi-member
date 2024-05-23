<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\EnumExtractableTrait;
final class Relation extends Enum
{
    use EnumExtractableTrait;
    public const AND = 'AND';
    public const OR = 'OR';
}
