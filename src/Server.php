<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;


use Throwable;
use ReflectionException;
use DI\NotFoundException;
use DI\DependencyException;
use Rabbit\Web\RequestContext;
use Rabbit\Web\RequestHandler;
use Rabbit\Base\Core\Exception;
use Rabbit\Web\ResponseContext;
use Rabbit\Base\Helper\FileHelper;
use Rabbit\Server\ServerDispatcher;
use Rabbit\Base\Contract\InitInterface;
use Rabbit\HttpServer\Middleware\ReqHandlerMiddleware;

/**
 * Class Server
 * @package Rabbit\HttpServer
 */
class Server extends \Rabbit\Server\Server implements InitInterface
{
    private string $request = Request::class;
    private string $response = Response::class;
    private array $middlewares = [];

    /**
     * @throws DependencyException
     * @throws Exception
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function init(): void
    {
        if (!$this->dispatcher) {
            $this->dispatcher = create(ServerDispatcher::class, [
                'requestHandler' => create(RequestHandler::class, [
                    'middlewares' => $this->middlewares ? $this->middlewares : [
                        create(ReqHandlerMiddleware::class)
                    ]
                ])
            ]);
        }
        if (!is_dir(dirname($this->setting['log_file']))) {
            FileHelper::createDirectory(dirname($this->setting['log_file']));
        }
    }

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @throws Throwable
     */
    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response): void
    {
        $psrRequest = $this->request;
        $psrResponse = $this->response;
        try {
            $psrRequest = new $psrRequest($request);
            $psrResponse = new $psrResponse($response);
            RequestContext::set($psrRequest);
            ResponseContext::set($psrResponse);
            $this->dispatcher->dispatch($psrRequest, $psrResponse);
            $psrResponse->send();
        } catch (Throwable $throw) {
            $errorResponse = getDI('errorResponse', false);
            if ($errorResponse === null) {
                $response->status(500);
                $response->end("An internal server error occurred.");
            } else {
                $response->end($errorResponse->handle($throw, $response));
            }
        }
    }

    /**
     * @return \Swoole\Server
     */
    protected function createServer(): \Swoole\Server
    {
        return new \Swoole\Http\Server($this->host, $this->port, $this->type);
    }

    /**
     * @param \Swoole\Server|null $server
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function startServer(\Swoole\Server $server = null): void
    {
        parent::startServer($server);
        $server->on('request', [$this, 'onRequest']);
        $server->start();
    }
}
