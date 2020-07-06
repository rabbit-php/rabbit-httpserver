<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\WebSocket;

use Rabbit\Base\Helper\JsonHelper;
use Rabbit\HttpServer\RouteInterface;
use Rabbit\Server\ServerDispatcher;
use Rabbit\Web\ErrorHandlerInterface;
use Swoole\WebSocket\Frame;

/**
 * Class Route
 * @package rabbit\httpserver\websocket
 */
class Route implements RouteInterface
{
    /** @var array */
    protected array $routes = [];
    /** @var ServerDispatcher */
    protected ServerDispatcher $dispatcher;
    /** @var CloseHandlerInterface */
    protected CloseHandlerInterface $closeHandler;
    /** @var \Swoole\Http\Response[] */
    protected array $responses = [];

    /**
     * Server constructor.
     * @param ServerDispatcher $dispatcher
     * @param array $routes
     */
    public function __construct(ServerDispatcher $dispatcher, array $routes = [])
    {
        $this->dispatcher = $dispatcher;
        $this->routes = $routes;
    }

    /**
     * @param $server
     * @return mixed|void
     */
    public function handle($server)
    {
        foreach ($this->routes as $handShake => $route) {
            foreach ($route as $item) {
                $server->handle(
                    $item,
                    function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use (
                        $handShake,
                        $item
                    ) {
                        try {
                            if (is_string($handShake) && $handShake = getDI($handShake) && $handShake instanceof HandShakeInterface) {
                                if (!$handShake->checkHandshake($request, $response)) {
                                    return;
                                }
                            }
                            $response->upgrade();
                            $this->responses[$item][$response->fd] = $response;
                            while (true) {
                                /** @var Frame $frame */
                                $frame = $response->recv();
                                if ($frame->opcode === 0x08) {
                                    unset($this->responses[$response->fd]);
                                    !empty($this->closeHandler) && $this->closeHandler->handle($frame);
                                    return;
                                }
                                $data = JsonHelper::decode($frame->data, true);
                                $psrRequest = new Request($data, $request);
                                $psrResponse = new Response($response);
                                $this->dispatcher->dispatch($psrRequest, $psrResponse);
                            }
                        } catch (\Throwable $throw) {
                            /**
                             * @var ErrorHandlerInterface $errorHandler
                             */
                            $errorHandler = getDI('errorHandler');
                            $errorHandler->handle($throw)->send();
                        }
                    }
                );
            }
        }
    }

    /**
     * @param string|null $route
     * @return array
     */
    public function getSwooleResponses(?string $route = null): array
    {
        if ($route === null) {
            return $this->responses;
        }
        return isset($this->responses[$route]) ? $this->responses[$route] : [];
    }
}
