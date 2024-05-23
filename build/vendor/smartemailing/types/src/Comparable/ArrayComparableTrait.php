<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types\Comparable;

use FapiMember\Library\SmartEmailing\Types\ToArrayInterface;
trait ArrayComparableTrait
{
    public function equals(mixed $that): bool
    {
        return $this instanceof ToArrayInterface && $that instanceof ToArrayInterface && \get_class($that) === static::class && $this->toArray() === $that->toArray();
    }
}
