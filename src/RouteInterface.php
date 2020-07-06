<?php
declare(strict_types=1);

namespace Rabbit\HttpServer;

/**
 * Interface RouteInterface
 * @package Rabbit\HttpServer
 */
interface RouteInterface
{
    /**
     * @param $server
     * @return mixed
     */
    public function handle($server);
}
