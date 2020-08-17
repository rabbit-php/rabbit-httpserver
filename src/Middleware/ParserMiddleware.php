<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rabbit\HttpServer\Parser\RequestParser;
use Rabbit\HttpServer\Parser\RequestParserInterface;
use Throwable;

/**
 * Class ParserMiddleware
 * @package Rabbit\HttpServer\Middleware
 */
class ParserMiddleware implements MiddlewareInterface
{
    /**
     * @var RequestParserInterface
     */
    private RequestParserInterface $parser;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->parser === null) {
            $this->parser = getDI(RequestParser::class);
        }
        $request = $this->parser->parse($request);
        return $handler->handle($request);
    }
}
