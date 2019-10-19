<?php


namespace rabbit\httpserver;

use rabbit\contract\DispatcherInterface;

/**
 * Interface RouteInterface
 * @package rabbit\httpserver
 */
interface RouteInterface
{
    /**
     * @param $server
     * @return mixed
     */
    public function handle($server);
}
