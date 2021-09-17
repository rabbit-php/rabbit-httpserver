<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\WebSocket;

use Swoole\Websocket\Frame;

/**
 * Interface CloseHandlerInterface
 * @package Rabbit\HttpServer\WebSocket
 */
interface CloseHandlerInterface
{
    public function handle(Frame $frame): void;
}
