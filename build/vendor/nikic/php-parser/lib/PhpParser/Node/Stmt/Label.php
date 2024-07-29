<?php

declare (strict_types=1);
namespace FapiMember\Library\PhpParser\Node\Stmt;

use FapiMember\Library\PhpParser\Node\Identifier;
use FapiMember\Library\PhpParser\Node\Stmt;
class Label extends Stmt
{
    /** @var Identifier Name */
    public Identifier $name;
    /**
     * Constructs a label node.
     *
     * @param string|Identifier $name Name
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct($name, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->name = \is_string($name) ? new Identifier($name) : $name;
    }
    public function getSubNodeNames(): array
    {
        return ['name'];
    }
    public function getType(): string
    {
        return 'Stmt_Label';
    }
}
