<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\Comparable\StringComparableTrait;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\StringExtractableTrait;
final class Iban implements ToStringInterface, ComparableInterface
{
    use StringExtractableTrait;
    use ToStringTrait;
    use StringComparableTrait;
    public const FORMAT_ELECTRONIC = 'electronic';
    public const FORMAT_PRINT = 'print';
    private \FapiMember\Library\Iban\Validation\Iban $iban;
    public function __construct(private string $value)
    {
        if (!\class_exists(\FapiMember\Library\Iban\Validation\Iban::class) || !\class_exists(\FapiMember\Library\Iban\Validation\Validator::class)) {
            throw new \Exception('For using ' . self::class . ' type, please require "jschaedl/iban-validation" in your project composer.');
        }
        $this->iban = new \FapiMember\Library\Iban\Validation\Iban($value);
        $validator = new \FapiMember\Library\Iban\Validation\Validator();
        if (!$validator->validate($this->iban)) {
            throw new InvalidTypeException('Invalid Iban: ' . $value);
        }
    }
    public function getValue(): string
    {
        return $this->value;
    }
    public function getCountry(): CountryCode
    {
        return CountryCode::from($this->iban->countryCode());
    }
    public function getFormatted(string $type = self::FORMAT_ELECTRONIC): string
    {
        return $this->iban->format($type);
    }
    public function getChecksum(): int
    {
        return (int) $this->iban->checksum();
    }
}
