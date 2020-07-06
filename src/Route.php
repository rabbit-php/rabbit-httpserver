<?php


namespace Rabbit\HttpServer;

use rabbit\contract\DispatcherInterface;
use rabbit\handler\ErrorHandlerInterface;
use rabbit\helper\ClassHelper;
use rabbit\helper\FileHelper;
use rabbit\server\ServerDispatcher;

/**
 * Class Route
 * @package rabbit\httpserver
 */
class Route implements RouteInterface
{
    /**
     * @var DispatcherInterface
     */
    protected $dispatcher;

    /**
     * Route constructor.
     * @param array $namespaces
     */
    public function __construct(ServerDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param $server
     * @param DispatcherInterface $dispatcher
     * @return mixed|void
     */
    public function handle($server)
    {
//        foreach ($this->routes as $route => $handler) {
//            $server->handle(
//                $route,
//                function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($route, $handler) {
//                    try {
//                        $psrRequest = new Request($request);
//                        if ($route === $request->server['request_uri']) {
//                            $psrRequest->withAttribute(self::ROUTE_CLASS, $handler);
//                        }
//                        $psrResponse = new Response($response);
//                        $this->dispatcher->dispatch($psrRequest, $psrResponse);
//                    } catch (\Throwable $throw) {
//                        /**
//                         * @var ErrorHandlerInterface $errorHandler
//                         */
//                        $errorHandler = getDI('errorHandler');
//                        $errorHandler->handle($throw)->send();
//                    }
//                }
//            );
//        }
        $server->handle('/', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            try {
                $psrRequest = new Request($request);
                $psrResponse = new Response($response);
                $this->dispatcher->dispatch($psrRequest, $psrResponse);
            } catch (\Throwable $throw) {
                /**
                 * @var ErrorHandlerInterface $errorHandler
                 */
                $errorHandler = getDI('errorHandler');
                $errorHandler->handle($throw)->send();
            }
        });
    }
}
