<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\Comparable\StringComparableTrait;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\FloatExtractableTrait;
final class ReLUValue implements ToStringInterface, ComparableInterface
{
    use FloatExtractableTrait;
    use ToStringTrait;
    use StringComparableTrait;
    public function __construct(private float $value)
    {
        if ($value < 0.0) {
            throw new InvalidTypeException('Invalid ReLU value: ' . $value);
        }
    }
    public function getValue(): float
    {
        return $this->value;
    }
}
