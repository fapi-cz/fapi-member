<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

interface ToArrayInterface
{
    /**
     * @return array<mixed>
     */
    public function toArray(): array;
}
