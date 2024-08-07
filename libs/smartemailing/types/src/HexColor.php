<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\Nette\Utils\Strings;
use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\Comparable\StringComparableTrait;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\StringExtractableTrait;
final class HexColor implements ToStringInterface, ComparableInterface
{
    use StringExtractableTrait;
    use ToStringTrait;
    use StringComparableTrait;
    private string $value;
    public function __construct(string $value)
    {
        $value = $this->preProcess($value);
        if (!$this->isValid($value)) {
            throw new InvalidTypeException('Invalid hex color string: ' . $value);
        }
        $this->value = $value;
    }
    public function getValue(): string
    {
        return $this->value;
    }
    private function isValid(string $value): bool
    {
        return (bool) \preg_match('#^\#([A-F0-9]{3}){1,2}\z#', $value);
    }
    private function preProcess(string $value): string
    {
        return Strings::upper($value);
    }
}
