<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 14:18
 */

namespace rabbit\httpserver\formater;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseRawFormater
 * @package rabbit\httpserver\formater
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
        $response = $response->withCharset($response->getCharset() ?? "UTF-8");
        // Content
        $data && $response = $response->withContent($data);

        return $response;
    }
}
