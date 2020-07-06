<?php
declare(strict_types=1);
namespace Rabbit\HttpServer\Parser;

use Psr\Http\Message\ServerRequestInterface;
use Rabbit\Base\Helper\ArrayHelper;
use Throwable;

/**
 * Class RequestParser
 * @package Rabbit\HttpServer\Parser
 */
class RequestParser implements RequestParserInterface
{
    /**
     * The parsers
     *
     * @var array
     */
    private array $parsers = [

    ];

    /**
     * The of header
     *
     * @var string
     */
    private string $headerKey = 'Content-type';

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws Throwable
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
        $parser = getDI($parserName);

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
