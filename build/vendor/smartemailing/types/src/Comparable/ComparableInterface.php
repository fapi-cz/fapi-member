<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types\Comparable;

interface ComparableInterface
{
    public function equals(mixed $that): bool;
}
