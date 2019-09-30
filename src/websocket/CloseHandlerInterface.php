<?php


namespace rabbit\httpserver\websocket;

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