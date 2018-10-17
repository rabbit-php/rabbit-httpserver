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
     *
     */
    public function start(): void
    {
        $this->createServer();
        $this->startServer();
    }

    /**
     *
     */
    protected function createServer(): void
    {
        $this->server = new swoole_http_server($this->config['host'], $this->config['port'], $this->config['type']);
    }

    /**
     * @throws \Exception
     */
    protected function startServer(): void
    {
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('shutdown', [$this, 'onShutdown']);

        $this->server->on('managerStart', [$this, 'onManagerStart']);

        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        $this->server->on('workerStop', [$this, 'onWorkerStop']);

        $this->server->on('request', [$this, 'onRequest']);

        $this->server->on('task', [$this, 'onTask']);
        $this->server->on('finish', [$this, 'onFinish']);

        $this->server->on('pipeMessage', [$this, 'onPipeMessage']);

        if (method_exists($this, 'onOpen')) {
            $this->server->on('open', [$this, 'onOpen']);
        }
        if (method_exists($this, 'onClose')) {
            $this->server->on('close', [$this, 'onClose']);
        }

        if (method_exists($this, 'onHandShake')) {
            $this->server->on('handshake', [$this, 'onHandShake']);
        }
        if (method_exists($this, 'onMessage')) {
            $this->server->on('message', [$this, 'onMessage']);
        }

        $this->server->set(ObjectFactory::get('server.setting'));
        $this->beforeStart();
        $this->server->start();
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