<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\WebSocket;

use Rabbit\Base\App;
use Swoole\Websocket\Frame;

/**
 * Class CloseHandler
 * @package rabbit\httpserver\websocket
 */
class CloseHandler implements CloseHandlerInterface
{
    public function handle(Frame $frame): void
    {
        App::warning(sprintf("The fd=%d is closed.code=%s reason=%s!", $frame->fd, $frame->code, $frame->reason));
    }
}
