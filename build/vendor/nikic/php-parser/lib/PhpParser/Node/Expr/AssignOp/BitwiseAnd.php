<?php

declare (strict_types=1);
namespace FapiMember\Library\PhpParser\Node\Expr\AssignOp;

use FapiMember\Library\PhpParser\Node\Expr\AssignOp;
class BitwiseAnd extends AssignOp
{
    public function getType(): string
    {
        return 'Expr_AssignOp_BitwiseAnd';
    }
}
