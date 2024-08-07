<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace FapiMember\Library\Nette\Http;

use FapiMember\Library\Nette;
use FapiMember\Library\Nette\Utils\DateTime;
/**
 * Rendering helpers for HTTP.
 */
final class Helpers
{
    use Nette\StaticClass;
    /** @internal */
    public const StrictCookieName = '_nss';
    /** @deprecated */
    public const STRICT_COOKIE_NAME = self::StrictCookieName;
    /**
     * Returns HTTP valid date format.
     * @param  string|int|\DateTimeInterface  $time
     */
    public static function formatDate($time): string
    {
        $time = DateTime::from($time)->setTimezone(new \DateTimeZone('GMT'));
        return $time->format('D, d M Y H:i:s \G\M\T');
    }
    /**
     * Is IP address in CIDR block?
     */
    public static function ipMatch(string $ip, string $mask): bool
    {
        [$mask, $size] = explode('/', $mask . '/');
        $tmp = function (int $n): string {
            return sprintf('%032b', $n);
        };
        $ip = implode('', array_map($tmp, unpack('N*', inet_pton($ip))));
        $mask = implode('', array_map($tmp, unpack('N*', inet_pton($mask))));
        $max = strlen($ip);
        if (!$max || $max !== strlen($mask) || (int) $size < 0 || (int) $size > $max) {
            return \false;
        }
        return strncmp($ip, $mask, ($size === '') ? $max : (int) $size) === 0;
    }
    public static function initCookie(IRequest $request, IResponse $response)
    {
        $response->setCookie(self::StrictCookieName, '1', 0, '/', null, null, \true, IResponse::SameSiteStrict);
    }
}
