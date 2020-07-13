<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Parser;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rabbit\Base\Helper\XmlHelper;


/**
 * Class RequestXmlParser
 * @package Rabbit\HttpServer\Parser
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
