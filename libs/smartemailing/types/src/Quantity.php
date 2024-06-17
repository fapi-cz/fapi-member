<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\Comparable\StringComparableTrait;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\IntExtractableTrait;
final class Quantity implements ToStringInterface, ComparableInterface
{
    use IntExtractableTrait;
    use ToStringTrait;
    use StringComparableTrait;
    public function __construct(private int $value)
    {
        if ($value < 1 || $value > \PHP_INT_MAX) {
            throw new InvalidTypeException('Invalid quantity: ' . $value);
        }
    }
    public function getValue(): int
    {
        return $this->value;
    }
}
