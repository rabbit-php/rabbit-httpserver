<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rabbit\HttpServer\Formater\IResponseFormatTool;
use Rabbit\HttpServer\Formater\ResponseFormater;
use Throwable;

/**
 * Trait AcceptTrait
 * @package Rabbit\HttpServer\Middleware
 */
trait AcceptTrait
{
    /**
     * @var IResponseFormatTool
     */
    protected IResponseFormatTool $formater;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws Throwable
     */
    protected function handleAccept(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Only handle HTTP-Server Response
        if (!$response instanceof ResponseInterface) {
            return $response;
        }
        if ($this->formater === null) {
            $this->formater = getDI(ResponseFormater::class);
        }

        $response = $this->formater->format($request, $response);
        return $response;
    }
}
