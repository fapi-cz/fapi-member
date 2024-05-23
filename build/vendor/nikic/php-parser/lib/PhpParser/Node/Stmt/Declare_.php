<?php

declare (strict_types=1);
namespace FapiMember\Library\PhpParser\Node\Stmt;

use FapiMember\Library\PhpParser\Node;
use FapiMember\Library\PhpParser\Node\DeclareItem;
class Declare_ extends Node\Stmt
{
    /** @var DeclareItem[] List of declares */
    public array $declares;
    /** @var Node\Stmt[]|null Statements */
    public ?array $stmts;
    /**
     * Constructs a declare node.
     *
     * @param DeclareItem[] $declares List of declares
     * @param Node\Stmt[]|null $stmts Statements
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(array $declares, ?array $stmts = null, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->declares = $declares;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames(): array
    {
        return ['declares', 'stmts'];
    }
    public function getType(): string
    {
        return 'Stmt_Declare';
    }
}
