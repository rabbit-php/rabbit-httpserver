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
use rabbit\framework\contract\HandlerInterface;
use rabbit\framework\core\Context;
use rabbit\framework\core\ObjectFactory;
use rabbit\framework\handler\ErrorHandlerInterface;
use rabbit\framework\handler\RequestHandlerInterface;
use rabbit\server\ServerDispatcher;

class HttpDispatcher extends ServerDispatcher
{
    /**
     * @var array
     */
    private $handlers = [];

    public function dispatch(...$params)
    {
        /**
         * @var RequestInterface $request
         * @var ResponseInterface $response
         */
        list($request, $response) = $params;
        try {
            // before dispatcher
            $this->beforeDispatch($request, $response);
            foreach ($this->handlers as $name => $handler) {
                /**
                 * @var RequestHandlerInterface $handler
                 */
                $response = $handler->handle($request);
            }
        } catch (\Throwable $throwable) {
            /**
             * @var ErrorHandlerInterface $errorHandler
             */
//            $errorHandler = ObjectFactory::get('errorHandler');
//            $errorHandler->handle($throwable);
            throw new $throwable;
        }
        $this->afterDispatch($response);
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