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
namespace FapiMember\Library\Fidry\Console;

use FapiMember\Library\Fidry\Console\DependencyInjection\Compiler\AddConsoleCommandPass;
use FapiMember\Library\Fidry\Console\DependencyInjection\FidryConsoleExtension;
use FapiMember\Library\Symfony\Component\DependencyInjection\Compiler\PassConfig;
use FapiMember\Library\Symfony\Component\DependencyInjection\ContainerBuilder;
use FapiMember\Library\Symfony\Component\HttpKernel\Bundle\Bundle;
use FapiMember\Library\Symfony\Component\HttpKernel\DependencyInjection\Extension;
final class FidryConsoleBundle extends Bundle
{
    public function getContainerExtension(): Extension
    {
        return new FidryConsoleExtension();
    }
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(
            new AddConsoleCommandPass(),
            PassConfig::TYPE_BEFORE_REMOVING,
            // Priority must be higher than Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass in FrameworkBundle
            10
        );
    }
}
