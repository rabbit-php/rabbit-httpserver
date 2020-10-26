<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\Parser;

use Rabbit\Base\Helper\JsonHelper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RequestJsonParser
 * @package Rabbit\HttpServer\Parser
 */
class RequestJsonParser implements RequestParserInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function parse(ServerRequestInterface $request): ServerRequestInterface
    {
        if ($request instanceof RequestInterface && strtoupper($request->getMethod()) !== 'GET') {
            $bodyStream = $request->getBody();
            $bodyContent = $bodyStream->getContents();
            $bodyContent = empty($bodyContent) ? "{}" : $bodyContent;
            try {
                $bodyParams = JsonHelper::decode($bodyContent, true);
            } catch (\Exception $e) {
                $bodyParams = $bodyContent;
            }
            $bodyParams = array_merge($request->getParsedBody(), $bodyParams);
            return $request->withParsedBody($bodyParams);
        }

        return $request;
    }
}
