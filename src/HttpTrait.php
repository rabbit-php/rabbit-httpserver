<?php

namespace rabbit\httpserver;

use rabbit\framework\contract\DispatcherInterface;
use rabbit\framework\core\ObjectFactory;
use rabbit\web\Request;
use swoole_http_request;
use swoole_http_response;

trait HttpTrait
{
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
    public function onRequest($request, $response)
    {
        $psrRequest = $this->request;
        $psrResponse = $this->response;
        $this->dispatcher->dispatch(new $psrRequest($request), new $psrResponse($response));
    }
}