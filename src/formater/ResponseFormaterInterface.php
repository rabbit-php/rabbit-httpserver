<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 14:56
 */

namespace rabbit\httpserver\formater;

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
