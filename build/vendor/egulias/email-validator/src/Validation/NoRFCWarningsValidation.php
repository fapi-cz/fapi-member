<?php

namespace FapiMember\Library\Egulias\EmailValidator\Validation;

use FapiMember\Library\Egulias\EmailValidator\EmailLexer;
use FapiMember\Library\Egulias\EmailValidator\Result\InvalidEmail;
use FapiMember\Library\Egulias\EmailValidator\Result\Reason\RFCWarnings;
class NoRFCWarningsValidation extends RFCValidation
{
    /**
     * @var InvalidEmail|null
     */
    private $error;
    /**
     * {@inheritdoc}
     */
    public function isValid(string $email, EmailLexer $emailLexer): bool
    {
        if (!parent::isValid($email, $emailLexer)) {
            return \false;
        }
        if (empty($this->getWarnings())) {
            return \true;
        }
        $this->error = new InvalidEmail(new RFCWarnings(), '');
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function getError(): ?InvalidEmail
    {
        return $this->error ?: parent::getError();
    }
}
