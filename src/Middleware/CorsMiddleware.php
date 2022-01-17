<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\Middleware;

use Rabbit\Web\ResponseContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CorsMiddleware
 * @package Rabbit\HttpServer\Middleware
 */
class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(private array $config)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = ResponseContext::get();
        foreach ($this->config as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        if (strtolower($request->getMethod()) === "options") {
            return $response;
        } else {
            ResponseContext::set($response);
        }
        return $handler->handle($request);
    }
}
