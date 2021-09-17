<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;

use Swoole\Coroutine\Http\Server;
use Swoole\Coroutine\Server as CoroutineServer;

/**
 * Interface RouteInterface
 * @package Rabbit\HttpServer
 */
interface RouteInterface
{
    public function handle(Server|CoroutineServer $server): void;
}
