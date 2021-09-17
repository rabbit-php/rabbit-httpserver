<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\WebSocket;

/**
 * Interface HandShakeInterface
 * @package Rabbit\HttpServer\WebSocket
 */
interface HandShakeInterface
{
    public function checkHandshake(\Swoole\Http\Request $request, \Swoole\Http\Response $response): bool;
}
