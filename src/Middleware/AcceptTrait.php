<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rabbit\HttpServer\Formater\ResponseFormater;
use Rabbit\HttpServer\Formater\IResponseFormatTool;

/**
 * Trait AcceptTrait
 * @package Rabbit\HttpServer\Middleware
 */
trait AcceptTrait
{
    protected ?IResponseFormatTool $formater = null;

    /**
     * @Author Albert 63851587@qq.com
     * @DateTime 2020-10-23
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param [type] $data
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleAccept(ServerRequestInterface $request, ResponseInterface $response, &$data): ResponseInterface
    {
        // Only handle HTTP-Server Response
        if (!$response instanceof ResponseInterface) {
            return $response;
        }
        if ($this->formater === null) {
            $this->formater = getDI(ResponseFormater::class);
        }

        $response = $this->formater->format($request, $response, $data);
        return $response;
    }
}
