<?php

declare (strict_types=1);
namespace FapiMember\Library\PhpParser\Node\Stmt;

use FapiMember\Library\PhpParser\Node;
class Echo_ extends Node\Stmt
{
    /** @var Node\Expr[] Expressions */
    public array $exprs;
    /**
     * Constructs an echo node.
     *
     * @param Node\Expr[] $exprs Expressions
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(array $exprs, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->exprs = $exprs;
    }
    public function getSubNodeNames(): array
    {
        return ['exprs'];
    }
    public function getType(): string
    {
        return 'Stmt_Echo';
    }
}
