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
    private array $middlewares = [];

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
            $psrRequest = new Request($request);
            $psrResponse = new Response();
            RequestContext::set($psrRequest);
            ResponseContext::set($psrResponse);
            $this->dispatcher->dispatch($psrRequest)->setSwooleResponse($response)->send();
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
