<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/8
 * Time: 19:44
 */

namespace Rabbit\HttpServer;

use rabbit\core\ObjectFactory;
use rabbit\core\SingletonTrait;
use rabbit\handler\ErrorHandlerInterface;

/**
 * Class Server
 * @package rabbit\httpserver
 */
class Server extends \rabbit\server\Server
{
    /**
     * @var string
     */
    private $request;

    /**
     * @var string
     */
    private $response;
    /** @var callable */
    private $errorResponse;

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response): void
    {
        $psrRequest = $this->request['class'];
        $psrResponse = $this->response['class'];
        try {
            $this->dispatcher->dispatch(new $psrRequest($request), new $psrResponse($response));
        } catch (\Throwable $throw) {
            try {
                /**
                 * @var ErrorHandlerInterface $errorHandler
                 */
                $errorHandler = ObjectFactory::get('errorHandler');
                $errorHandler->handle($throw)->send();
            } catch (\Throwable $throwable) {
                if (is_callable($this->errorResponse)) {
                    call_user_func($this->errorResponse, $response, $throwable);
                } else {
                    $response->status(500);
                    $response->end("An internal server error occurred.");
                }
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
     * @throws \Exception
     */
    protected function startServer(\Swoole\Server $server = null): void
    {
        parent::startServer($server);
        $server->on('request', [$this, 'onRequest']);
        $server->start();
    }
}
