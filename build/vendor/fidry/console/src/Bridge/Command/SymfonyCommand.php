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
use FapiMember\Library\Fidry\Console\Command\CommandAware;
use FapiMember\Library\Fidry\Console\Command\CommandRegistry;
use FapiMember\Library\Fidry\Console\Command\DecoratesSymfonyCommand;
use FapiMember\Library\Fidry\Console\Command\InitializableCommand;
use FapiMember\Library\Fidry\Console\Command\InteractiveCommand;
use FapiMember\Library\Fidry\Console\IO;
use FapiMember\Library\Symfony\Component\Console\Application;
use FapiMember\Library\Symfony\Component\Console\Command\Command as BaseSymfonyCommand;
use FapiMember\Library\Symfony\Component\Console\Input\InputInterface;
use FapiMember\Library\Symfony\Component\Console\Output\OutputInterface;
/**
 * Implements a Symfony command based on a Fidry command.
 */
final class SymfonyCommand extends BaseSymfonyCommand
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private IO $io;
    /** @psalm-suppress PropertyNotSetInConstructor */
    private CommandRegistry $commandRegistry;
    public function __construct(private readonly Command $command)
    {
        $name = $command->getConfiguration()->getName();
        parent::__construct($name);
    }
    public function setApplication(?Application $application = null): void
    {
        parent::setApplication($application);
        $decoratedCommand = $this->command;
        if ($decoratedCommand instanceof DecoratesSymfonyCommand) {
            $decoratedCommand->getDecoratedCommand()->setApplication($application);
        }
        if (null !== $application) {
            $this->commandRegistry = new CommandRegistry($application);
        }
    }
    protected function configure(): void
    {
        $configuration = $this->command->getConfiguration();
        $this->setDescription($configuration->getDescription())->setHelp($configuration->getHelp())->setHidden($configuration->isHidden());
        $definition = $this->getDefinition();
        $definition->setArguments($configuration->getArguments());
        $definition->setOptions($configuration->getOptions());
    }
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new IO($input, $output);
        $command = $this->command;
        if ($command instanceof CommandAware) {
            $command->setCommandRegistry($this->commandRegistry);
        }
        if ($command instanceof InitializableCommand) {
            $command->initialize($this->io);
        }
    }
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $command = $this->command;
        if ($command instanceof InteractiveCommand) {
            $command->interact($this->io);
        }
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->command->execute($this->io);
    }
}
