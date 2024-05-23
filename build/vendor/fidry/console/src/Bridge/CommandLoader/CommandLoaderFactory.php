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
namespace FapiMember\Library\Fidry\Console\Bridge\CommandLoader;

use FapiMember\Library\Fidry\Console\Command\Command as FidryCommand;
use FapiMember\Library\Fidry\Console\Command\LazyCommandEnvelope;
use FapiMember\Library\Symfony\Component\Console\Command\Command as SymfonyCommand;
use FapiMember\Library\Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
interface CommandLoaderFactory
{
    /**
     * @param array<LazyCommandEnvelope|FidryCommand|SymfonyCommand> $commands
     */
    public function createCommandLoader(array $commands): CommandLoaderInterface;
}
