<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\Nette\Utils\Strings;
use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\Comparable\StringComparableTrait;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\StringExtractableTrait;
final class NonEmptyString implements ToStringInterface, ComparableInterface
{
    use StringExtractableTrait;
    use ToStringTrait;
    use StringComparableTrait;
    private string $value;
    public function __construct(string $value)
    {
        $value = Strings::trim($value);
        if ($value === '') {
            throw new InvalidTypeException('Value must be non empty string.');
        }
        $this->value = $value;
    }
    public function getValue(): string
    {
        return $this->value;
    }
}
