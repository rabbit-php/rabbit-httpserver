<?php
declare(strict_types=1);

namespace Rabbit\HttpServer;

use Swoole\Http\Response;
use Throwable;

/**
 * Interface IErrorResponse
 * @package Rabbit\HttpServer
 */
interface IErrorResponse
{
    public function handle(Response $response, Throwable $throwable): void;
}