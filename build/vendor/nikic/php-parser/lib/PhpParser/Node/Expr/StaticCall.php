<?php

declare (strict_types=1);
namespace FapiMember\Library\PhpParser\Node\Expr;

use FapiMember\Library\PhpParser\Node;
use FapiMember\Library\PhpParser\Node\Arg;
use FapiMember\Library\PhpParser\Node\Expr;
use FapiMember\Library\PhpParser\Node\Identifier;
use FapiMember\Library\PhpParser\Node\VariadicPlaceholder;
class StaticCall extends CallLike
{
    /** @var Node\Name|Expr Class name */
    public Node $class;
    /** @var Identifier|Expr Method name */
    public Node $name;
    /** @var array<Arg|VariadicPlaceholder> Arguments */
    public array $args;
    /**
     * Constructs a static method call node.
     *
     * @param Node\Name|Expr $class Class name
     * @param string|Identifier|Expr $name Method name
     * @param array<Arg|VariadicPlaceholder> $args Arguments
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(Node $class, $name, array $args = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->class = $class;
        $this->name = \is_string($name) ? new Identifier($name) : $name;
        $this->args = $args;
    }
    public function getSubNodeNames(): array
    {
        return ['class', 'name', 'args'];
    }
    public function getType(): string
    {
        return 'Expr_StaticCall';
    }
    public function getRawArgs(): array
    {
        return $this->args;
    }
}
