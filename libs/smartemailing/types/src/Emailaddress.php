<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\Egulias\EmailValidator\EmailValidator;
use FapiMember\Library\Egulias\EmailValidator\Validation\RFCValidation;
use FapiMember\Library\Nette\Utils\Strings;
use FapiMember\Library\Nette\Utils\Validators;
use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\Comparable\StringComparableTrait;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\StringExtractableTrait;
final class Emailaddress implements ToStringInterface, ComparableInterface
{
    use StringExtractableTrait;
    use ToStringTrait;
    use StringComparableTrait;
    private string $value;
    private string $localPart;
    private HostName $hostName;
    private function __construct(string $value)
    {
        try {
            $ok = $this->initialize($value);
        } catch (\Throwable $e) {
            $ok = \false;
        }
        if (!$ok) {
            throw new InvalidEmailaddressException('Invalid emailaddress: ' . $value);
        }
    }
    public function getLocalPart(): string
    {
        return $this->localPart;
    }
    public function getValue(): string
    {
        return $this->value;
    }
    /**
     * @deprecated Use getHostName() instead
     */
    public function getDomain(): Domain
    {
        return Domain::from($this->hostName->getValue());
    }
    public function getHostName(): HostName
    {
        return $this->hostName;
    }
    public static function preprocessEmailaddress(string $emailaddress): string
    {
        $sanitized = Strings::lower(Strings::toAscii(Strings::trim($emailaddress)));
        return \strtr($sanitized, ['>' => '', '<' => '']);
    }
    private function initialize(string $emailaddress): bool
    {
        $emailaddress = self::preprocessEmailaddress($emailaddress);
        if (!Strings::contains($emailaddress, '@') || Strings::contains($emailaddress, '"') || Strings::contains($emailaddress, ' ') || !Validators::isEmail($emailaddress)) {
            return \false;
        }
        $validator = new EmailValidator();
        $isValid = $validator->isValid($emailaddress, new RFCValidation());
        if (!$isValid) {
            return \false;
        }
        $exploded = \explode('@', $emailaddress);
        [$this->localPart, $hostName] = $exploded;
        $this->hostName = HostName::from($hostName);
        $this->value = $emailaddress;
        return \true;
    }
}
