<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rabbit\HttpServer\Exceptions\NotFoundHttpException;
use Rabbit\Web\AttributeEnum;
use Rabbit\Web\ResponseContext;
use Throwable;

/**
 * Class EndMiddleware
 * @package Rabbit\HttpServer\Middleware
 */
class EndMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = explode('/', ltrim($request->getUri()->getPath(), '/'));
        if (count($route) < 2) {
            throw new NotFoundHttpException("can not find the route:$route");
        }
        $controller = 'apis';
        foreach ($route as $index => $value) {
            if ($index === count($route) - 1) {
                $action = $value;
            } elseif ($index === count($route) - 2) {
                $controller .= '\controllers\\' . ucfirst($value) . 'Controller';
            } else {
                $controller .= '\\' . $value;
            }
        }
        $controller = getDI($controller);
        if ($controller === null) {
            throw new NotFoundHttpException("can not find the route:$route");
        }
        /**
         * @var ResponseInterface $response
         */
        $response = call_user_func_array([$controller, $action], $request->getQueryParams());
        if (!$response instanceof ResponseInterface) {
            $newResponse = ResponseContext::get();
            $newResponse->withAttribute(AttributeEnum::RESPONSE_ATTRIBUTE, $response);
        }

        return $handler->handle($request);
    }
}
