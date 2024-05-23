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
namespace FapiMember\Library\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt;

use FapiMember\Library\Humbug\PhpScoper\NotInstantiable;
use FapiMember\Library\PhpParser\Node\Name;
use FapiMember\Library\PhpParser\Node\Stmt\Namespace_;
use FapiMember\Library\PhpParser\NodeVisitorAbstract;
/**
 * @private
 */
final class NamespaceManipulator extends NodeVisitorAbstract
{
    use NotInstantiable;
    private const ORIGINAL_NAME_ATTRIBUTE = 'originalName';
    public static function hasOriginalName(Namespace_ $namespace): bool
    {
        return $namespace->hasAttribute(self::ORIGINAL_NAME_ATTRIBUTE);
    }
    public static function getOriginalName(Namespace_ $namespace): ?Name
    {
        if (!self::hasOriginalName($namespace)) {
            return $namespace->name;
        }
        return $namespace->getAttribute(self::ORIGINAL_NAME_ATTRIBUTE);
    }
    public static function setOriginalName(Namespace_ $namespace, ?Name $originalName): void
    {
        $namespace->setAttribute(self::ORIGINAL_NAME_ATTRIBUTE, $originalName);
    }
}
