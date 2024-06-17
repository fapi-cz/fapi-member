<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\Comparable\StringComparableTrait;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\FloatExtractableTrait;
final class SigmoidValue implements ToStringInterface, ComparableInterface
{
    use FloatExtractableTrait;
    use ToStringTrait;
    use StringComparableTrait;
    public function __construct(private float $value)
    {
        if ($value < -1 || $value > 1) {
            throw new InvalidTypeException('Invalid sigmoid value: ' . $value);
        }
    }
    public function getValue(): float
    {
        return $this->value;
    }
}
