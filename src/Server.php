<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/8
 * Time: 19:44
 */

namespace rabbit\httpserver;

use Psr\Http\Message\ServerRequestInterface;
use rabbit\web\Request;
use rabbit\web\Response;
use swoole_http_server;
use rabbit\framework\core\SingletonTrait;

class Server extends \rabbit\servers\Server
{
    use HttpTrait;

    /**
     * @var string
     */
    private $request = Request::class;

    /**
     * @var string
     */
    private $response = Response::class;

    public function start()
    {
        $this->createServer();
        $this->startServer();
    }

    protected function createServer()
    {
        $this->server = new swoole_http_server($this->config['host'], $this->config['port'], $this->config['type']);
    }

    protected function startServer()
    {
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('shutdown', [$this, 'onShutdown']);

        $this->server->on('managerStart', [$this, 'onManagerStart']);

        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        $this->server->on('workerStop', [$this, 'onWorkerStop']);

        $this->server->on('request', [$this, 'onRequest']);

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

        if (method_exists($this, 'onTask')) {
            $this->server->on('task', [$this, 'onTask']);
        }
        if (method_exists($this, 'onFinish')) {
            $this->server->on('finish', [$this, 'onFinish']);
        }
        if (method_exists($this, 'onPipeMessage')) {
            $this->server->on('pipeMessage', [$this, 'onPipeMessage']);
        }

        $this->server->set($this->config['server']);
        $this->beforeStart();
        $this->server->start();
    }
}