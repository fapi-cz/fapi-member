<?php

namespace FapiMember\Library\Egulias\EmailValidator\Result;

use FapiMember\Library\Egulias\EmailValidator\Result\Reason\SpoofEmail as ReasonSpoofEmail;
class SpoofEmail extends InvalidEmail
{
    public function __construct()
    {
        $this->reason = new ReasonSpoofEmail();
        parent::__construct($this->reason, '');
    }
}
