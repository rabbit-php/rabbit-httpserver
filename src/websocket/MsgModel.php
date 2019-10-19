<?php


namespace rabbit\httpserver\websocket;

use rabbit\App;
use rabbit\httpserver\CoServer;

class MsgModel
{
    /** @var mixed */
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle(): void
    {
        /** @var CoServer $server */
        $server = App::getServer();
        $responses = $server->wsRoute->getSwooleResponses();
        foreach ($responses as $fd => $response) {
            $response->push(is_string($this->params) ? $this->params : json_encode(
                $this->params,
                JSON_UNESCAPED_UNICODE
            ));
        }
    }
}
