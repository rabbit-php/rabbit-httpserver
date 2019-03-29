<?php

namespace rabbit\httpserver\parser;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use rabbit\helper\XmlHelper;

/**
 * Class RequestXmlParser
 * @package rabbit\httpserver\parser
 */
class RequestXmlParser implements RequestParserInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function parse(ServerRequestInterface $request): ServerRequestInterface
    {
        if ($request instanceof RequestInterface) {
            $bodyContent = $request->getBody()->getContents();
            try {
                $bodyParams = XmlHelper::decode($bodyContent);
            } catch (\Exception $e) {
                $bodyParams = $bodyContent;
            }
            $bodyParams = array_merge($request->getParsedBody(), $bodyParams);
            return $request->withParsedBody($bodyParams);
        }

        return $request;
    }
}
