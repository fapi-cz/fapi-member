<?php

namespace FapiMember\Library\Egulias\EmailValidator\Parser;

use FapiMember\Library\Egulias\EmailValidator\Result\Result;
use FapiMember\Library\Egulias\EmailValidator\Result\InvalidEmail;
use FapiMember\Library\Egulias\EmailValidator\Result\Reason\CommentsInIDRight;
class IDLeftPart extends LocalPart
{
    protected function parseComments(): Result
    {
        return new InvalidEmail(new CommentsInIDRight(), $this->lexer->current->value);
    }
}
