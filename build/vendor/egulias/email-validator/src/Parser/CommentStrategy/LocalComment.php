<?php

namespace FapiMember\Library\Egulias\EmailValidator\Parser\CommentStrategy;

use FapiMember\Library\Egulias\EmailValidator\EmailLexer;
use FapiMember\Library\Egulias\EmailValidator\Result\Result;
use FapiMember\Library\Egulias\EmailValidator\Result\ValidEmail;
use FapiMember\Library\Egulias\EmailValidator\Warning\CFWSNearAt;
use FapiMember\Library\Egulias\EmailValidator\Result\InvalidEmail;
use FapiMember\Library\Egulias\EmailValidator\Result\Reason\ExpectingATEXT;
class LocalComment implements CommentStrategy
{
    /**
     * @var array
     */
    private $warnings = [];
    public function exitCondition(EmailLexer $lexer, int $openedParenthesis): bool
    {
        return !$lexer->isNextToken(EmailLexer::S_AT);
    }
    public function endOfLoopValidations(EmailLexer $lexer): Result
    {
        if (!$lexer->isNextToken(EmailLexer::S_AT)) {
            return new InvalidEmail(new ExpectingATEXT('ATEX is not expected after closing comments'), $lexer->current->value);
        }
        $this->warnings[CFWSNearAt::CODE] = new CFWSNearAt();
        return new ValidEmail();
    }
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
