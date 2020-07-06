<?php


namespace Rabbit\HttpServer\WebSocket;

/**
 * Interface CloseHandlerInterface
 * @package rabbit\httpserver\websocket
 */
interface CloseHandlerInterface
{
    /**
     * @param \Swoole\Websocket\Frame $frame
     */
    public function handle(\Swoole\Websocket\Frame $frame): void;
}
