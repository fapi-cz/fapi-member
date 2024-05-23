<?php

namespace FapiMember\Library\Egulias\EmailValidator\Validation;

use FapiMember\Library\Egulias\EmailValidator\EmailLexer;
use FapiMember\Library\Egulias\EmailValidator\EmailParser;
use FapiMember\Library\Egulias\EmailValidator\Result\InvalidEmail;
use FapiMember\Library\Egulias\EmailValidator\Result\Reason\ExceptionFound;
use FapiMember\Library\Egulias\EmailValidator\Warning\Warning;
class RFCValidation implements EmailValidation
{
    /**
     * @var Warning[]
     */
    private array $warnings = [];
    /**
     * @var ?InvalidEmail
     */
    private $error;
    public function isValid(string $email, EmailLexer $emailLexer): bool
    {
        $parser = new EmailParser($emailLexer);
        try {
            $result = $parser->parse($email);
            $this->warnings = $parser->getWarnings();
            if ($result->isInvalid()) {
                /** @psalm-suppress PropertyTypeCoercion */
                $this->error = $result;
                return \false;
            }
        } catch (\Exception $invalid) {
            $this->error = new InvalidEmail(new ExceptionFound($invalid), '');
            return \false;
        }
        return \true;
    }
    public function getError(): ?InvalidEmail
    {
        return $this->error;
    }
    /**
     * @return Warning[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
