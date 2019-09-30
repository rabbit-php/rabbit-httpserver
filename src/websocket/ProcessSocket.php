<?php


namespace rabbit\httpserver\websocket;

use rabbit\parser\ParserInterface;
use rabbit\parser\PhpParser;
use rabbit\server\ProcessSocketInterface;

/**
 * Class ProcessSocket
 * @package rabbit\httpserver\websocket
 */
class ProcessSocket implements ProcessSocketInterface
{
    /** @var array */
    protected $sockets = [];
    /** @var ParserInterface */
    protected $parser;

    /**
     * ProcessSocket constructor.
     */
    public function __construct()
    {
        $this->parser = new PhpParser();
    }

    /**
     * @param array $responses
     * @param string $data
     * @return mixed|void
     */
    public function handle($server, string $data)
    {
        [$route, $msg] = json_decode($data, true);
        $responses = $server->wsRoute->getSwooleResponses($route);
        foreach ($responses as $fd => $response) {
            $response->push($msg);
        }
    }
}