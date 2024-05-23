<?php

declare (strict_types=1);
namespace FapiMember\Library\Swoole\WebSocket;

class CloseFrame extends Frame
{
    public $opcode = 8;
    public $code = 1000;
    public $reason = '';
}
