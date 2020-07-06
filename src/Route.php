<?php
declare(strict_types=1);

namespace Rabbit\HttpServer;

use Rabbit\Server\ServerDispatcher;
use Rabbit\Web\DispatcherInterface;
use Rabbit\Web\ErrorHandlerInterface;

/**
 * Class Route
 * @package Rabbit\HttpServer
 */
class Route implements RouteInterface
{
    /**
     * @var DispatcherInterface
     */
    protected DispatcherInterface $dispatcher;

    /**
     * Route constructor.
     * @param ServerDispatcher $dispatcher
     */
    public function __construct(ServerDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param $server
     * @return mixed|void
     */
    public function handle($server)
    {
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
