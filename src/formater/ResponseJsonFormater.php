<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Formater;

use Psr\Http\Message\ResponseInterface;
use Rabbit\Base\Helper\ArrayHelper;
use Rabbit\Base\Helper\JsonHelper;

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
        $response = $response->withCharset($response->getCharset() ?? "UTF-8");

        // Content
        $data = ArrayHelper::toArray(['data' => $data]);
        $content = JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
        $response = $response->withContent($content);

        return $response;
    }
}
