<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\Nette\Utils\Strings;
use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\Comparable\StringComparableTrait;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\StringExtractableTrait;
final class Hex32 implements ToStringInterface, ComparableInterface
{
    use StringExtractableTrait;
    use ToStringTrait;
    use StringComparableTrait;
    private string $value;
    private function __construct(string $value)
    {
        if (!$this->isValid($value)) {
            throw new InvalidTypeException('Invalid hex string: ' . $value);
        }
        $this->value = Strings::lower($value);
    }
    public static function fromGuid(Guid $guid): Hex32
    {
        return self::from(Strings::replace($guid->getValue(), '/-/', ''));
    }
    public function getValue(): string
    {
        return $this->value;
    }
    private function isValid(string $value): bool
    {
        if (Strings::length($value) !== 32) {
            return \false;
        }
        return \ctype_xdigit($value);
    }
}
