<?php

declare (strict_types=1);
namespace FapiMember\Library\PhpParser\ErrorHandler;

use FapiMember\Library\PhpParser\Error;
use FapiMember\Library\PhpParser\ErrorHandler;
/**
 * Error handler that handles all errors by throwing them.
 *
 * This is the default strategy used by all components.
 */
class Throwing implements ErrorHandler
{
    public function handleError(Error $error): void
    {
        throw $error;
    }
}
