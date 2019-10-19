<?php


namespace rabbit\httpserver;

use rabbit\App;
use rabbit\exception\InvalidArgumentException;
use rabbit\server\Server;

/**
 * Class CoServer
 * @package rabbit\httpserver
 */
class CoServer extends \rabbit\server\CoServer
{
    /** @var RouteInterface */
    public $route;
    /** @var RouteInterface */
    public $wsRoute;

    /**
     * @return Server
     */
    protected function createServer()
    {
        return new \Co\Http\Server($this->host, $this->port, $this->ssl, true);
    }

    /**
     * @throws \Exception
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
