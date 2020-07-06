<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Formater;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseFormaterInterface
 * @package rabbit\httpserver\formater
 */
interface ResponseFormaterInterface
{
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function format(ResponseInterface $response, $data): ResponseInterface;
}
