<?php


namespace rabbit\httpserver\websocket;


use rabbit\contract\DispatcherInterface;
use rabbit\handler\ErrorHandlerInterface;
use rabbit\helper\JsonHelper;
use rabbit\httpserver\RouteInterface;
use rabbit\server\ServerDispatcher;
use Swoole\WebSocket\Frame;

/**
 * Class Route
 * @package rabbit\httpserver\websocket
 */
class Route implements RouteInterface
{
    /** @var array */
    protected $routes = [];
    /** @var ServerDispatcher */
    protected $dispatcher;
    /** @var CloseHandlerInterface */
    protected $closeHandler;
    /** @var \Swoole\Http\Response[] */
    protected $responses = [];

    /**
     * Server constructor.
     * @param array $setting
     * @param array $coSetting
     * @throws \Exception
     */
    public function __construct(ServerDispatcher $dispatcher, array $routes = [])
    {
        $this->dispatcher = $dispatcher;
        $this->routes = $routes;
    }

    /**
     * @param $server
     * @param DispatcherInterface $dispatcher
     * @return mixed|void
     */
    public function handle($server)
    {
        foreach ($this->routes as $handShake => $route) {
            foreach ($route as $item) {
                $server->handle($item,
                    function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use (
                        $handShake,
                        $item
                    ) {
                        try {
                            if (is_string($handShake) && $handShake = getDI($handShake) && $handShake instanceof HandShakeInterface) {
                                if (!$handShake->checkHandshake($psrRequest, $psrResponse)) {
                                    return;
                                }
                            }
                            $response->upgrade();
                            $this->responses[$item][$response->fd] = $response;
                            while (true) {
                                /** @var Frame $frame */
                                $frame = $response->recv();
                                if ($frame->opcode === 0x08) {
                                    if (is_string($this->closeHandler)) {
                                        $this->closeHandler = getDI($this->closeHandler);
                                    }
                                    unset($this->responses[$response->fd]);
                                    !empty($this->closeHandler) && $this->closeHandler->handle($frame);
                                    return;
                                }
                                $psrRequest = new Request($data, $request);
                                $psrResponse = new Response($response);
                                $data = JsonHelper::decode($frame->data, true);
                                $this->dispatcher->dispatch($psrRequest, $psrResponse);
                            }

                        } catch (\Throwable $throw) {
                            /**
                             * @var ErrorHandlerInterface $errorHandler
                             */
                            $errorHandler = getDI('errorHandler');
                            $errorHandler->handle($throw)->send();
                        }
                    });
            }
        }
    }

    /**
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