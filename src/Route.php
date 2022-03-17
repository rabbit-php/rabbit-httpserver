<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;

use Rabbit\Web\RequestContext;
use Rabbit\Web\ResponseContext;
use Rabbit\Web\DispatcherInterface;
use Swoole\Coroutine\Http\Server;
use Swoole\Coroutine\Server as CoroutineServer;

/**
 * Class Route
 * @package Rabbit\HttpServer
 */
class Route implements RouteInterface
{
    public function __construct(protected DispatcherInterface $dispatcher)
    {
    }

    public function handle(Server|CoroutineServer $server): void
    {
        $server->handle('/', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response): void {
            try {
                $data = [
                    'server' => $request->server,
                    'header' => $request->header,
                    'query' => $request->get,
                    'body' => $request->post,
                    'content' => $request->rawContent(),
                    'cookie' => $request->cookie,
                    'files' => $request->files,
                    'fd' => $request->fd,
                    'request' => $request,
                ];
                $psrRequest = new Request($data);
                $psrResponse = new Response();
                RequestContext::set($psrRequest);
                ResponseContext::set($psrResponse);
                $this->dispatcher->dispatch($psrRequest)->setSwooleResponse($response)->send();
            } catch (\Throwable $throw) {
                $errorResponse = service('errorResponse', false);
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
