<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types\Comparable;

use FapiMember\Library\SmartEmailing\Types\ToStringInterface;
trait StringComparableTrait
{
    public function equals(mixed $that): bool
    {
        return $this instanceof ToStringInterface && $that instanceof ToStringInterface && \get_class($that) === static::class && (string) $this === (string) $that;
    }
}
