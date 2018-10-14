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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rabbit\contract\HandlerInterface;
use rabbit\core\Context;
use rabbit\handler\ErrorHandlerInterface;
use rabbit\server\ServerDispatcher;

class HttpDispatcher extends ServerDispatcher
{
    /**
     * @var RequestHandlerInterface
     */
    private $requestHandler;

    public function dispatch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // before dispatcher
            $this->beforeDispatch($request, $response);
            $requestHandler = new ($this->requestHandler)($this->middlewares);
            $response = $requestHandler->handle($request);
        } catch (\Throwable $throwable) {
            /**
             * @var ErrorHandlerInterface $errorHandler
             */
//            $errorHandler = ObjectFactory::get('errorHandler');
//            $errorHandler->handle($throwable);
            throw new $throwable;
        }
        $this->afterDispatch($response);
        return $response;
    }

    protected function beforeDispatch(RequestInterface $request, ResponseInterface $response)
    {
        Context::set('request', $request);
        Context::set('response', $response);
    }

    protected function afterDispatch(ResponseInterface $response)
    {
        $response->send();
        Context::release();
    }
}