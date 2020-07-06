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
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function format(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
