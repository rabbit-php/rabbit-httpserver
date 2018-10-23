<?php

namespace rabbit\httpserver\parser;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RequestParserInterface
 * @package rabbit\httpserver\parser
 */
interface RequestParserInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function parse(ServerRequestInterface $request): ServerRequestInterface;
}
