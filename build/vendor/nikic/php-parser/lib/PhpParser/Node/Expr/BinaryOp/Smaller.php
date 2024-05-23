<?php

declare (strict_types=1);
namespace FapiMember\Library\PhpParser\Node\Expr\BinaryOp;

use FapiMember\Library\PhpParser\Node\Expr\BinaryOp;
class Smaller extends BinaryOp
{
    public function getOperatorSigil(): string
    {
        return '<';
    }
    public function getType(): string
    {
        return 'Expr_BinaryOp_Smaller';
    }
}
