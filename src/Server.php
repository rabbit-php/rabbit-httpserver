<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/8
 * Time: 19:44
 */

namespace rabbit\httpserver;

use rabbit\core\ObjectFactory;
use rabbit\core\SingletonTrait;
use rabbit\handler\ErrorHandlerInterface;
use swoole_http_server;

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
     * 执行请求
     *
     * @param swoole_http_request $request
     * @param swoole_http_response $response
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
        return new swoole_http_server($this->host, $this->port, $this->type);
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