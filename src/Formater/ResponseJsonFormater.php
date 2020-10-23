<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\Formater;

use Rabbit\Base\Helper\JsonHelper;
use Rabbit\Base\Helper\ArrayHelper;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseJsonFormater
 * @package Rabbit\HttpServer\Formater
 */
class ResponseJsonFormater implements ResponseFormaterInterface
{

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function format(ResponseInterface $response, $data): ResponseInterface
    {
        // Headers
        $response = $response->withoutHeader('Content-Type')->withAddedHeader('Content-Type', 'application/json');

        // Content
        $data = [
            'code' => 0,
            'msg' => 'success',
            'result' => ArrayHelper::toArray($data)
        ];
        $content = JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
        $body = $response->getBody();
        $body->seek(0);
        $body->write($content);

        return $response;
    }
}
