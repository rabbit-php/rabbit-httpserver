<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/8
 * Time: 19:44
 */

namespace rabbit\httpserver;

use rabbit\contract\DispatcherInterface;
use rabbit\core\ObjectFactory;
use rabbit\core\SingletonTrait;
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
        $server->set(ObjectFactory::get('server.setting'));
        $server->start();
    }

    /**
     * @var DispatcherInterface
     */
    private $dispatcher;

    /**
     * 执行请求
     *
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     */
    public function onRequest($request, $response): void
    {
        $psrRequest = $this->request['class'];
        $psrResponse = $this->response['class'];
        $this->dispatcher->dispatch(new $psrRequest($request), new $psrResponse($response));
    }
}