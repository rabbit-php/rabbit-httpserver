<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;


use Rabbit\Base\Exception\InvalidArgumentException;
use Swoole\Coroutine\Http\Server;
use Swoole\Coroutine\Server as CoroutineServer;

/**
 * Class CoServer
 * @package Rabbit\HttpServer
 */
class CoServer extends \Rabbit\Server\CoServer
{
    public RouteInterface $route;

    public RouteInterface $wsRoute;

    protected function createServer(): CoroutineServer|Server
    {
        return new CoroutineServer($this->host, $this->port, $this->ssl, true);
    }

    protected function startServer(CoroutineServer|Server $server): void
    {
        parent::startServer($server);
        if (!$this->route instanceof RouteInterface) {
            throw new InvalidArgumentException("The Route must be set!");
        }
        $this->route->handle($server);
        if ($this->wsRoute) {
            $this->wsRoute->handle($server);
        }
        $server->start();
    }
}
