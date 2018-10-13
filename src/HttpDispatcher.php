<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/9
 * Time: 11:57
 */

namespace rabbit\httpserver;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use rabbit\framework\contract\DispatcherInterface;
use rabbit\framework\core\Context;
use rabbit\servers\ServerDispatcher;

class HttpDispatcher extends ServerDispatcher
{
    public function dispatch(...$params)
    {
        /**
         * @var RequestInterface $request
         * @var ResponseInterface $response
         */
        list($request, $response) = $params;
        // before dispatcher
        $this->beforeDispatch($request, $response);
        $this->afterDispatch($response);
    }

    protected function beforeDispatch(RequestInterface $request, ResponseInterface $response)
    {
        Context::set('request', $request);
        Context::set('response', $response);
    }

    protected function afterDispatch(ResponseInterface $response)
    {
        $response->withContent(123)->send();
        Context::release();
    }
}