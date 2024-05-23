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
namespace FapiMember\Library\Humbug\PhpScoper\PhpParser\Node;

use FapiMember\Library\PhpParser\Node\Arg;
use FapiMember\Library\PhpParser\Node\Expr\ConstFetch;
use FapiMember\Library\PhpParser\Node\Expr\FuncCall;
use FapiMember\Library\PhpParser\Node\Name\FullyQualified;
use FapiMember\Library\PhpParser\Node\Scalar\String_;
final class ClassAliasFuncCall extends FuncCall
{
    public function __construct(FullyQualified $prefixedName, FullyQualified $originalName, array $attributes = [])
    {
        parent::__construct(new FullyQualified('class_alias'), [new Arg(new String_((string) $prefixedName)), new Arg(new String_((string) $originalName)), new Arg(new ConstFetch(new FullyQualified('false')))], $attributes);
    }
}
