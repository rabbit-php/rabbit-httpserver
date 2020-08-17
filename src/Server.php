<?php
declare(strict_types=1);

namespace Rabbit\HttpServer;


use DI\DependencyException;
use DI\NotFoundException;
use Rabbit\Base\Contract\InitInterface;
use Rabbit\Base\Core\Exception;
use Rabbit\Base\Helper\FileHelper;
use Rabbit\HttpServer\Middleware\ReqHandlerMiddleware;
use Rabbit\HttpServer\Middleware\StartMiddleware;
use Rabbit\Server\ServerDispatcher;
use Rabbit\Web\RequestHandler;
use ReflectionException;
use Throwable;

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
                        create(StartMiddleware::class),
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
            $this->dispatcher->dispatch(new $psrRequest($request), new $psrResponse($response));
        } catch (Throwable $throw) {
            $errorResponse = getDI('errorResponse', false);
            if ($errorResponse === null) {
                $response->status(500);
                $response->end("An internal server error occurred.");
            } else {
                $errorResponse->handle($response, $throw);
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
