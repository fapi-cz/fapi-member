<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\Comparable\StringComparableTrait;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\FloatExtractableTrait;
final class Part implements ToStringInterface, ComparableInterface
{
    use FloatExtractableTrait;
    use ToStringTrait;
    use StringComparableTrait;
    public function __construct(private float $value)
    {
        if ($value < 0 || $value > 1) {
            throw new InvalidTypeException('Invalid part: ' . $value);
        }
    }
    public static function fromRatio(float $value, float $whole): self
    {
        if ($value > $whole) {
            throw new InvalidTypeException('Value cannot be higher than whole: but ' . $value . ' / ' . $whole . ' given.');
        }
        if ($whole === 0.0) {
            return new static(0.0);
        }
        return new static($value / $whole);
    }
    public function getValue(): float
    {
        return $this->value;
    }
    public function getPercent(): float
    {
        return $this->getValue() * 100;
    }
}
