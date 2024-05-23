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
namespace FapiMember\Library\Humbug\PhpScoper\PhpParser;

use FapiMember\Library\Humbug\PhpScoper\PhpParser\NodeVisitor\ExcludedFunctionExistsEnricher;
use FapiMember\Library\Humbug\PhpScoper\PhpParser\NodeVisitor\ExcludedFunctionExistsStringNodeStack;
use FapiMember\Library\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt\NamespaceStmtCollection;
use FapiMember\Library\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\IdentifierResolver;
use FapiMember\Library\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt\UseStmtCollection;
use FapiMember\Library\Humbug\PhpScoper\Scoper\PhpScoper;
use FapiMember\Library\Humbug\PhpScoper\Symbol\EnrichedReflector;
use FapiMember\Library\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use FapiMember\Library\PhpParser\NodeTraverser as PhpParserNodeTraverser;
use FapiMember\Library\PhpParser\NodeTraverserInterface;
use FapiMember\Library\PhpParser\NodeVisitor as PhpParserNodeVisitor;
use FapiMember\Library\PhpParser\NodeVisitor\NameResolver;
/**
 * @private
 */
class TraverserFactory
{
    public function __construct(private readonly EnrichedReflector $reflector, private readonly string $prefix, private readonly SymbolsRegistry $symbolsRegistry)
    {
    }
    public function create(PhpScoper $scoper): NodeTraverserInterface
    {
        return self::createTraverser(self::createNodeVisitors($this->prefix, $this->reflector, $scoper, $this->symbolsRegistry));
    }
    /**
     * @param PhpParserNodeVisitor[] $nodeVisitors
     */
    private static function createTraverser(array $nodeVisitors): NodeTraverserInterface
    {
        $traverser = new NodeTraverser(new PhpParserNodeTraverser());
        foreach ($nodeVisitors as $nodeVisitor) {
            $traverser->addVisitor($nodeVisitor);
        }
        return $traverser;
    }
    /**
     * @return PhpParserNodeVisitor[]
     */
    private static function createNodeVisitors(string $prefix, EnrichedReflector $reflector, PhpScoper $scoper, SymbolsRegistry $symbolsRegistry): array
    {
        $namespaceStatements = new NamespaceStmtCollection();
        $useStatements = new UseStmtCollection();
        $nameResolver = new NameResolver(null, ['preserveOriginalNames' => \true]);
        $identifierResolver = new IdentifierResolver($nameResolver);
        $stringNodePrefixer = new StringNodePrefixer($scoper);
        $excludedFunctionExistsStringNodeStack = new ExcludedFunctionExistsStringNodeStack();
        return [$nameResolver, new NodeVisitor\AttributeAppender\ParentNodeAppender(), new NodeVisitor\AttributeAppender\IdentifierNameAppender($identifierResolver), new NodeVisitor\NamespaceStmt\NamespaceStmtPrefixer($prefix, $reflector, $namespaceStatements), new NodeVisitor\UseStmt\UseStmtCollector($namespaceStatements, $useStatements), new NodeVisitor\UseStmt\UseStmtPrefixer($prefix, $reflector), new NodeVisitor\FunctionIdentifierRecorder($prefix, $identifierResolver, $symbolsRegistry, $reflector), new NodeVisitor\ClassIdentifierRecorder($prefix, $identifierResolver, $symbolsRegistry, $reflector), new NodeVisitor\NameStmtPrefixer($prefix, $namespaceStatements, $useStatements, $reflector), new NodeVisitor\StringScalarPrefixer($prefix, $reflector, $excludedFunctionExistsStringNodeStack), new NodeVisitor\NewdocPrefixer($stringNodePrefixer), new NodeVisitor\EvalPrefixer($stringNodePrefixer), new NodeVisitor\ClassAliasStmtAppender($identifierResolver, $symbolsRegistry), new ExcludedFunctionExistsEnricher($prefix, $excludedFunctionExistsStringNodeStack), new NodeVisitor\MultiConstStmtReplacer(), new NodeVisitor\ConstStmtReplacer($identifierResolver, $reflector)];
    }
}
