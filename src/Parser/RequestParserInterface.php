<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Parser;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RequestParserInterface
 * @package Rabbit\HttpServer\Parser
 */
interface RequestParserInterface
{
    public function parse(ServerRequestInterface $request): ServerRequestInterface;
}
