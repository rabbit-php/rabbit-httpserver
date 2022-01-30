<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\Middleware;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Rabbit\HttpServer\Parser\RequestParser;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rabbit\HttpServer\Parser\RequestParserInterface;

/**
 * Class ParserMiddleware
 * @package Rabbit\HttpServer\Middleware
 */
class ParserMiddleware implements MiddlewareInterface
{
    public function __construct(private ?RequestParserInterface $parser = null)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->parser === null) {
            $this->parser = create(RequestParser::class);
        }
        $request = $this->parser->parse($request);
        return $handler->handle($request);
    }
}
