<?php
declare(strict_types=1);

namespace Rabbit\HttpServer;


use DI\DependencyException;
use DI\NotFoundException;
use Rabbit\Web\ErrorHandlerInterface;

/**
 * Class Server
 * @package Rabbit\HttpServer
 */
class Server extends \Rabbit\Server\Server
{
    /**
     * @var string
     */
    private string $request = Request::class;

    /**
     * @var string
     */
    private string $response = Response::class;

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response): void
    {
        $psrRequest = $this->request;
        $psrResponse = $this->response;
        try {
            $this->dispatcher->dispatch(new $psrRequest($request), new $psrResponse($response));
        } catch (\Throwable $throw) {
            try {
                /**
                 * @var ErrorHandlerInterface $errorHandler
                 */
                $errorHandler = getDI('errorHandler');
                $errorHandler->handle($throw)->send();
            } catch (\Throwable $throwable) {
                $response->status(500);
                $response->end("An internal server error occurred.");
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
