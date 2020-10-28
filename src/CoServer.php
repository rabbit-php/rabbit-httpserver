<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;


use Rabbit\Base\Exception\InvalidArgumentException;

/**
 * Class CoServer
 * @package Rabbit\HttpServer
 */
class CoServer extends \Rabbit\Server\CoServer
{
    /** @var RouteInterface */
    public RouteInterface $route;
    /** @var RouteInterface */
    public RouteInterface $wsRoute;

    /**
     * @return \Co\Http\Server
     */
    protected function createServer()
    {
        return new \Co\Http\Server($this->host, $this->port, $this->ssl, true);
    }

    /**
     * @param null $server
     */
    protected function startServer($server = null): void
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
