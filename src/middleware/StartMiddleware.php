<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 14:59
 */

namespace rabbit\httpserver\middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rabbit\core\ObjectFactory;
use rabbit\governance\trace\TraceInterface;
use rabbit\server\AttributeEnum;

/**
 * Class StartMiddleware
 * @package rabbit\httpserver\middleware
 */
class StartMiddleware implements MiddlewareInterface
{
    use AcceptTrait;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (($tracer = ObjectFactory::get('tracer', null, false)) !== null) {
            $data = $request->getAttribute(AttributeEnum::TRACE_ATTRIBUTE);
            /** @var TraceInterface $tracer */
            $data && $tracer->release($data['traceId']);
        }

        return $this->handleAccept($request, $response);
    }

}