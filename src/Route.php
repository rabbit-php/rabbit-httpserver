<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;

use Rabbit\Web\RequestContext;
use Rabbit\Web\ResponseContext;
use Rabbit\Server\ServerDispatcher;
use Rabbit\Web\DispatcherInterface;
use Swoole\Coroutine\Http\Server;
use Swoole\Coroutine\Server as CoroutineServer;

/**
 * Class Route
 * @package Rabbit\HttpServer
 */
class Route implements RouteInterface
{
    protected DispatcherInterface $dispatcher;

    public function __construct(ServerDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function handle(Server|CoroutineServer $server): void
    {
        $server->handle('/', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            try {
                $psrRequest = new Request($request);
                $psrResponse = new Response();
                RequestContext::set($psrRequest);
                ResponseContext::set($psrResponse);
                $this->dispatcher->dispatch($psrRequest)->setSwooleResponse($response)->send();
            } catch (\Throwable $throw) {
                $errorResponse = getDI('errorResponse', false);
                if ($errorResponse === null) {
                    $response->status(500);
                    $response->end("An internal server error occurred.");
                } else {
                    $response->end($errorResponse->handle($throw, $response));
                }
            }
        });
    }
}
