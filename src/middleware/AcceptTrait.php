<?php

namespace Rabbit\HttpServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use rabbit\core\ObjectFactory;
use rabbit\httpserver\formater\IResponseFormatTool;
use rabbit\httpserver\formater\ResponseFormater;

/**
 * Trait AcceptTrait
 * @package rabbit\httpserver\middleware
 */
trait AcceptTrait
{
    /**
     * @var IResponseFormatTool
     */
    protected $formater = ResponseFormater::class;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws \InvalidArgumentException
     */
    protected function handleAccept(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Only handle HTTP-Server Response
        if (!$response instanceof ResponseInterface) {
            return $response;
        }
        if (is_string($this->formater)) {
            $this->formater = ObjectFactory::get($this->formater);
        }

        $response = $this->formater->format($request, $response);
        return $response;
    }
}
