<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\Middleware;

use Throwable;
use Rabbit\Web\ResponseContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rabbit\HttpServer\Exceptions\NotFoundHttpException;

/**
 * Class ReqHandlerMiddleware
 * @package Rabbit\HttpServer\Middleware
 */
class ReqHandlerMiddleware implements MiddlewareInterface
{
    use AcceptTrait;
    protected string $prefix = 'Apis';
    protected string $handlers = 'Handlers';
    protected bool $isUper = true;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = explode('/', ltrim($request->getUri()->getPath(), '/'));
        if (count($route) !== 2) {
            throw new NotFoundHttpException("the route type error:" . $request->getUri()->getPath());
        }
        list($module, $action) = $route;
        $class = ($this->prefix ? $this->prefix . '\\' : '')
            . ($this->isUper ? ucfirst($module) : $module) . '\\'
            . ($this->handlers ? $this->handlers . '\\' : '')
            . ($this->isUper ? ucfirst($action) : $action);

        $class = getDI($class, false);
        if ($class === null) {
            throw new NotFoundHttpException("can not find the route:" . $request->getUri()->getPath());
        }

        $response = $class($request);
        if (!$response instanceof ResponseInterface) {
            $newResponse = ResponseContext::get();
            return $this->handleAccept($request, $newResponse, $response);
        }

        return $handler->handle($request);
    }
}
