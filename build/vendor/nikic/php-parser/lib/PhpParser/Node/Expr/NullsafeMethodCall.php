<?php

declare (strict_types=1);
namespace FapiMember\Library\PhpParser\Node\Expr;

use FapiMember\Library\PhpParser\Node;
use FapiMember\Library\PhpParser\Node\Arg;
use FapiMember\Library\PhpParser\Node\Expr;
use FapiMember\Library\PhpParser\Node\Identifier;
use FapiMember\Library\PhpParser\Node\VariadicPlaceholder;
class NullsafeMethodCall extends CallLike
{
    /** @var Expr Variable holding object */
    public Expr $var;
    /** @var Identifier|Expr Method name */
    public Node $name;
    /** @var array<Arg|VariadicPlaceholder> Arguments */
    public array $args;
    /**
     * Constructs a nullsafe method call node.
     *
     * @param Expr $var Variable holding object
     * @param string|Identifier|Expr $name Method name
     * @param array<Arg|VariadicPlaceholder> $args Arguments
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(Expr $var, $name, array $args = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->var = $var;
        $this->name = \is_string($name) ? new Identifier($name) : $name;
        $this->args = $args;
    }
    public function getSubNodeNames(): array
    {
        return ['var', 'name', 'args'];
    }
    public function getType(): string
    {
        return 'Expr_NullsafeMethodCall';
    }
    public function getRawArgs(): array
    {
        return $this->args;
    }
}
