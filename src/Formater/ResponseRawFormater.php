<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Formater;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseRawFormater
 * @package Rabbit\HttpServer\Formater
 */
class ResponseRawFormater implements ResponseFormaterInterface
{
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function format(ResponseInterface $response, $data): ResponseInterface
    {
        // Headers
        $response = $response->withoutHeader('Content-Type')->withAddedHeader('Content-Type', 'text/plain');
        // Content
        $body = $response->getBody();
        $body->seek(0);
        $body->write($data);

        return $response;
    }
}
