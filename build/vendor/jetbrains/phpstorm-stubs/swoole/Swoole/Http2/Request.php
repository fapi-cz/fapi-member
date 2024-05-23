<?php

declare (strict_types=1);
namespace FapiMember\Library\Swoole\Http2;

class Request
{
    public $path = '/';
    public $method = 'GET';
    public $headers;
    public $cookies;
    public $data = '';
    public $pipeline = \false;
}
