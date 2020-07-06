<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rabbit\Base\Core\Context;

/**
 * Class CorsMiddleware
 * @package Rabbit\HttpServer\Middleware
 */
class CorsMiddleware implements MiddlewareInterface
{
    /** @var array */
    private array $config = [];

    /**
     * CorsMiddleware constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Context::get('response');
        foreach ($this->config as $name => $value) {
            $response->withHeader($name, $value);
        }
        if (strtolower($request->getMethod()) === "options") {
            return $response;
        }
        return $handler->handle($request);
    }
}
