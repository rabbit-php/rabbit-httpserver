<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;

use Throwable;
use Swoole\Http\Response;

/**
 * Interface IErrorResponse
 * @package Rabbit\HttpServer
 */
interface IErrorResponse
{
    public function handle(Throwable $throwable, Response $response): string;
}
