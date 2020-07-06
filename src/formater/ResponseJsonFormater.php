<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 13:52
 */

namespace Rabbit\HttpServer\Formater;

use Psr\Http\Message\ResponseInterface;
use rabbit\helper\ArrayHelper;
use rabbit\helper\JsonHelper;

/**
 * Class ResponseJsonFormater
 * @package rabbit\httpserver\formater
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
