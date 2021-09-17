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
    private array $parsers = [

    ];

    private string $headerKey = 'Content-type';

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

    private function mergeParsers(): array
    {
        return ArrayHelper::merge($this->parsers, $this->defaultParsers());
    }

    public function defaultParsers(): array
    {
        return [
            'application/json' => RequestJsonParser::class,
            'application/xml' => RequestXmlParser::class,
        ];
    }
}
