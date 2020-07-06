<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/16
 * Time: 14:49
 */

namespace Rabbit\HttpServer\WebSocket;

/**
 * Interface HandShakeInterface
 * @package rabbit\httpserver
 */
interface HandShakeInterface
{
    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @return bool
     */
    public function checkHandshake(\Swoole\Http\Request $request, \Swoole\Http\Response $response): bool;
}
