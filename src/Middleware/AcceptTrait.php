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

    protected function handleAccept(ServerRequestInterface $request, ResponseInterface $response, string|array|object|float|int|bool|null &$data): ResponseInterface
    {
        // Only handle HTTP-Server Response
        if (!$response instanceof ResponseInterface) {
            return $response;
        }
        if ($this->formater === null) {
            $this->formater = create(ResponseFormater::class);
        }

        $response = $this->formater->format($request, $response, $data);
        return $response;
    }
}
