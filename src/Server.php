<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;

use Throwable;
use Rabbit\Web\RequestContext;
use Rabbit\Web\RequestHandler;
use Rabbit\Web\ResponseContext;
use Rabbit\Base\Helper\FileHelper;
use Rabbit\Server\ServerDispatcher;
use Rabbit\Base\Contract\InitInterface;
use Rabbit\HttpServer\Middleware\ReqHandlerMiddleware;

class Server extends \Rabbit\Server\Server implements InitInterface
{
    protected array $middlewares = [];

    public function init(): void
    {
        if (!$this->dispatcher) {
            $this->dispatcher = create(ServerDispatcher::class, [
                'requestHandler' => create(RequestHandler::class, [
                    'middlewares' => $this->middlewares ? array_values($this->middlewares) : [
                        create(ReqHandlerMiddleware::class)
                    ]
                ])
            ]);
        }
        if (!is_dir(dirname($this->setting['log_file']))) {
            FileHelper::createDirectory(dirname($this->setting['log_file']));
        }
        unset($this->middlewares);
    }

    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response): void
    {
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
            $psrResponse->setSwooleResponse($response);
            RequestContext::set($psrRequest);
            ResponseContext::set($psrResponse);
            $this->dispatcher->dispatch($psrRequest)->send();
        } catch (Throwable $throw) {
            $errorResponse = service('errorResponse', false);
            if ($errorResponse === null) {
                $response->status(500);
                $response->end("An internal server error occurred.");
            } else {
                $response->end($errorResponse->handle($throw, $response));
            }
        }
    }

    protected function createServer(): \Swoole\Server
    {
        return new \Swoole\Http\Server($this->host, $this->port, $this->type);
    }

    protected function startServer(\Swoole\Server $server): void
    {
        parent::startServer($server);
        $server->on('request', [$this, 'onRequest']);
        $server->start();
    }
}
