<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\Formater;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface IResponseFormatTool
 * @package Rabbit\HttpServer\Formater
 */
interface IResponseFormatTool
{
    /**
     * @Author Albert 63851587@qq.com
     * @DateTime 2020-10-23
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param [type] $data
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function format(ServerRequestInterface $request, ResponseInterface $response, &$data): ResponseInterface;
}
