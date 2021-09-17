<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\WebSocket;

use Rabbit\Server\CoServer;
use Rabbit\Server\ServerHelper;

/**
 * Class MsgModel
 * @package Rabbit\HttpServer\WebSocket
 */
class MsgModel
{
    protected string|array|object|float|int|bool|null $params;

    public function __construct(string|array|object|float|int|bool|null $params)
    {
        $this->params = $params;
    }

    public function handle(): void
    {
        /** @var CoServer $server */
        $server = ServerHelper::getServer();
        $responses = $server->wsRoute->getSwooleResponses();
        foreach ($responses as $fd => $response) {
            $response->push(is_string($this->params) ? $this->params : json_encode(
                $this->params,
                JSON_UNESCAPED_UNICODE
            ));
        }
    }
}
