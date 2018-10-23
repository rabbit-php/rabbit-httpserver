<?php

namespace rabbit\httpserver\parser;

use Psr\Http\Message\ServerRequestInterface;
use rabbit\core\ObjectFactory;
use rabbit\helper\ArrayHelper;

/**
 * Class RequestParser
 * @package rabbit\httpserver\parser
 */
class RequestParser implements RequestParserInterface
{
    /**
     * The parsers
     *
     * @var array
     */
    private $parsers = [

    ];

    /**
     * The of header
     *
     * @var string
     */
    private $headerKey = 'Content-type';

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws \Exception
     */
    public function parse(ServerRequestInterface $request): ServerRequestInterface
    {
        $contentType = $request->getHeaderLine($this->headerKey);
        $parsers = $this->mergeParsers();

        if (!isset($parsers[$contentType])) {
            return $request;
        }

        /* @var RequestParserInterface $parser */
        $parserName = $parsers[$contentType];
        $parser = ObjectFactory::get($parserName);

        return $parser->parse($request);
    }

    /**
     * @return array
     */
    private function mergeParsers(): array
    {
        return ArrayHelper::merge($this->parsers, $this->defaultParsers());
    }

    /**
     * @return array
     */
    public function defaultParsers(): array
    {
        return [
            'application/json' => RequestJsonParser::class,
            'application/xml' => RequestXmlParser::class,
        ];
    }
}
