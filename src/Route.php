<?php


namespace rabbit\httpserver;


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
    const ROUTE_CLASS = 'route_class';
    /**
     * @var DispatcherInterface
     */
    protected $dispatcher;
    /** @var array */
    protected $routes = [];

    /**
     * Route constructor.
     * @param array $namespaces
     */
    public function __construct(ServerDispatcher $dispatcher, array $paths, array $include, string $prefix = '')
    {
        $this->dispatcher = $dispatcher;
        foreach ($paths as $dir => $remove) {
            FileHelper::findFiles($dir . '/' . $remove, [
                'filter' => function ($path) use ($include, $prefix, $remove) {
                    if (strpos($path, '.php') === false) {
                        return true;
                    }
                    $class = ClassHelper::getClassByString(file_get_contents($path));
                    $list = explode('\\', $class);
                    if (array_intersect($list, $include)) {
                        $route = str_replace(array_merge(['\\', $remove], $include),
                            ['/'], $class);
                        $route = strtolower($prefix . str_replace('//', '/', $route));
                        $this->routes[$route] = $class;
                    }
                    return false;
                }
            ]);
        }
    }

    /**
     * @param $server
     * @param DispatcherInterface $dispatcher
     * @return mixed|void
     */
    public function handle($server)
    {
        foreach ($this->routes as $route => $handler) {
            $server->handle($route,
                function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($handler) {
                    try {
                        $psrRequest = new Request($request);
                        $psrRequest->withAttribute(self::ROUTE_CLASS, $handler);
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
}