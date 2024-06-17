<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\Nette\Utils\Strings;
use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\Comparable\StringComparableTrait;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\StringExtractableTrait;
final class DomainName implements ToStringInterface, ComparableInterface
{
    use StringExtractableTrait;
    use ToStringTrait;
    use StringComparableTrait;
    private string $value;
    private function __construct(string $value)
    {
        $value = Strings::lower($value);
        $value = Strings::trim($value);
        if (!$this->isValid($value)) {
            throw new InvalidTypeException('Invalid domain: ' . $value);
        }
        $this->value = $value;
    }
    public function getValue(): string
    {
        return $this->value;
    }
    public function getSecondLevelDomain(): DomainName
    {
        $parts = \explode('.', $this->value);
        $numberOfKeptParts = 2;
        if (\count($parts) > 2 && \end($parts) === 'uk') {
            $numberOfKeptParts = 3;
        }
        $secondLevelParts = \array_slice($parts, -$numberOfKeptParts, $numberOfKeptParts);
        return self::from(\implode('.', $secondLevelParts));
    }
    private function isValid(string $value): bool
    {
        return \preg_match('/^([_a-z\d](-*[_a-z\d])*)(\.([_a-z\d](-*[_a-z\d])*))*$/i', $value) && \preg_match('/^.{1,253}$/', $value) && \preg_match('/^[^\.]{1,63}(\.[^\.]{1,63})*$/', $value);
    }
}
