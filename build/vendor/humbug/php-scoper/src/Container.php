<?php

declare (strict_types=1);
/*
 * This file is part of the humbug/php-scoper package.
 *
 * Copyright (c) 2017 Théo FIDRY <theo.fidry@gmail.com>,
 *                    Pádraic Brady <padraic.brady@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FapiMember\Library\Humbug\PhpScoper;

use FapiMember\Library\Humbug\PhpScoper\Configuration\ConfigurationFactory;
use FapiMember\Library\Humbug\PhpScoper\Configuration\RegexChecker;
use FapiMember\Library\Humbug\PhpScoper\Configuration\SymbolsConfigurationFactory;
use FapiMember\Library\Humbug\PhpScoper\PhpParser\Printer\Printer;
use FapiMember\Library\Humbug\PhpScoper\PhpParser\Printer\StandardPrinter;
use FapiMember\Library\Humbug\PhpScoper\Scoper\ScoperFactory;
use FapiMember\Library\Humbug\PhpScoper\Symbol\EnrichedReflectorFactory;
use FapiMember\Library\Humbug\PhpScoper\Symbol\Reflector;
use FapiMember\Library\PhpParser\Lexer;
use FapiMember\Library\PhpParser\Lexer\Emulative;
use FapiMember\Library\PhpParser\Parser;
use FapiMember\Library\PhpParser\Parser\Php7;
use FapiMember\Library\PhpParser\Parser\Php8;
use FapiMember\Library\PhpParser\PhpVersion;
use FapiMember\Library\PhpParser\PrettyPrinter\Standard;
use FapiMember\Library\Symfony\Component\Filesystem\Filesystem;
final class Container
{
    private Filesystem $filesystem;
    private ConfigurationFactory $configFactory;
    private Parser $parser;
    private Reflector $reflector;
    private ScoperFactory $scoperFactory;
    private EnrichedReflectorFactory $enrichedReflectorFactory;
    private Printer $printer;
    public function getFileSystem(): Filesystem
    {
        if (!isset($this->filesystem)) {
            $this->filesystem = new Filesystem();
        }
        return $this->filesystem;
    }
    public function getConfigurationFactory(): ConfigurationFactory
    {
        if (!isset($this->configFactory)) {
            $this->configFactory = new ConfigurationFactory($this->getFileSystem(), new SymbolsConfigurationFactory(new RegexChecker()));
        }
        return $this->configFactory;
    }
    public function getScoperFactory(): ScoperFactory
    {
        if (!isset($this->scoperFactory)) {
            $this->scoperFactory = new ScoperFactory($this->getParser(), $this->getEnrichedReflectorFactory(), $this->getPrinter());
        }
        return $this->scoperFactory;
    }
    public function getParser(): Parser
    {
        if (!isset($this->parser)) {
            $this->parser = $this->createParser();
        }
        return $this->parser;
    }
    private function createParser(): Parser
    {
        $version = PhpVersion::getNewestSupported();
        $lexer = $version->isHostVersion() ? new Lexer() : new Emulative($version);
        return ($version->id >= 80000) ? new Php8($lexer, $version) : new Php7($lexer, $version);
    }
    public function getReflector(): Reflector
    {
        if (!isset($this->reflector)) {
            $this->reflector = Reflector::createWithPhpStormStubs();
        }
        return $this->reflector;
    }
    public function getEnrichedReflectorFactory(): EnrichedReflectorFactory
    {
        if (!isset($this->enrichedReflectorFactory)) {
            $this->enrichedReflectorFactory = new EnrichedReflectorFactory($this->getReflector());
        }
        return $this->enrichedReflectorFactory;
    }
    public function getPrinter(): Printer
    {
        if (!isset($this->printer)) {
            $this->printer = new StandardPrinter(new Standard());
        }
        return $this->printer;
    }
}
