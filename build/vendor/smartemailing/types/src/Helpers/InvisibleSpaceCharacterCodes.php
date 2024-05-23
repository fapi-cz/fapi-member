<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types\Helpers;

abstract class InvisibleSpaceCharacterCodes
{
    /**
     * @var array<int>
     */
    private static array $codes = [0x9, 0xa, 0xb, 0xc, 0xd, 0x20, 0x85, 0xa0, 0x1680, 0x2000, 0x2001, 0x2002, 0x2003, 0x2004, 0x2005, 0x2006, 0x2007, 0x2008, 0x2009, 0x200a, 0x3000, 0xad, 0xf0, 0xc2ad, 0xca, 0xc2];
    /**
     * @return array<int>
     */
    final public static function getCodes(): array
    {
        return self::$codes;
    }
}
