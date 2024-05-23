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

use FapiMember\Library\Fidry\Console\Command\Command;
use FapiMember\Library\Fidry\Console\Command\Configuration;
use FapiMember\Library\Fidry\Console\Command\DecoratesSymfonyCommand;
use FapiMember\Library\Fidry\Console\IO;
use FapiMember\Library\Symfony\Component\Console\Command\Command as SymfonyCommand;
/**
 * Implements a Fidry command based on a Symfony command.
 *
 * @private
 */
final class ReversedSymfonyCommand implements Command, DecoratesSymfonyCommand
{
    public function __construct(private SymfonyCommand $command)
    {
    }
    public function getDecoratedCommand(): SymfonyCommand
    {
        return $this->command;
    }
    public function getConfiguration(): Configuration
    {
        return new Configuration($this->command->getName() ?? '', $this->command->getDescription(), $this->command->getHelp(), $this->command->getDefinition()->getArguments(), $this->command->getDefinition()->getOptions());
    }
    public function execute(IO $io): int
    {
        return $this->command->run($io->getInput(), $io->getOutput());
    }
}
