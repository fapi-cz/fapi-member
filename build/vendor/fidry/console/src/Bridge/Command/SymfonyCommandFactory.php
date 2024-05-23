<?php

/*
 * This file is part of the Fidry\Console package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace FapiMember\Library\Fidry\Console\Bridge\Command;

use Closure;
use FapiMember\Library\Fidry\Console\Command\Command as FidryCommand;
use FapiMember\Library\Symfony\Component\Console\Command\Command as BaseSymfonyCommand;
interface SymfonyCommandFactory
{
    public function crateSymfonyCommand(FidryCommand $command): BaseSymfonyCommand;
    /**
     * @param Closure(): FidryCommand $factory
     */
    public function crateSymfonyLazyCommand(string $name, string $description, Closure $factory): BaseSymfonyCommand;
}
