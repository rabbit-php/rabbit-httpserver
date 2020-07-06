<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\WebSocket;

use Rabbit\Base\App;
use Swoole\Websocket\Frame;
use Throwable;

/**
 * Class CloseHandler
 * @package rabbit\httpserver\websocket
 */
class CloseHandler implements CloseHandlerInterface
{
    /**
     * @param Frame $frame
     * @throws Throwable
     */
    public function handle(Frame $frame): void
    {
        App::warning(sprintf("The fd=%d is closed.code=%s reason=%s!", $frame->fd, $frame->code, $frame->reason));
    }
}
