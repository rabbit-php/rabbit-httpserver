<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\Formater;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseFormaterInterface
 * @package rabbit\httpserver\formater
 */
interface ResponseFormaterInterface
{
    public function format(ResponseInterface $response, string|array|object|float|int|bool|null &$data): ResponseInterface;
}
