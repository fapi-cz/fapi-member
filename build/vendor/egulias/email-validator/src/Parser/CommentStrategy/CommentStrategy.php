<?php

namespace FapiMember\Library\Egulias\EmailValidator\Parser\CommentStrategy;

use FapiMember\Library\Egulias\EmailValidator\EmailLexer;
use FapiMember\Library\Egulias\EmailValidator\Result\Result;
use FapiMember\Library\Egulias\EmailValidator\Warning\Warning;
interface CommentStrategy
{
    /**
     * Return "true" to continue, "false" to exit
     */
    public function exitCondition(EmailLexer $lexer, int $openedParenthesis): bool;
    public function endOfLoopValidations(EmailLexer $lexer): Result;
    /**
     * @return Warning[]
     */
    public function getWarnings(): array;
}
