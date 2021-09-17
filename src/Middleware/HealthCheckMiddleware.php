<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rabbit\Web\ResponseContext;

/**
 * Class HealthCheckMiddleware
 * @package Rabbit\HttpServer\Middleware
 */
class HealthCheckMiddleware implements MiddlewareInterface
{
    protected ?string $health;

    public function __construct(string $health = '/health')
    {
        $this->health = $health;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $url = $request->getUri()->getPath();
        if ($url === $this->health) {
            return ResponseContext::get();
        }
        return $handler->handle($request);
    }
}