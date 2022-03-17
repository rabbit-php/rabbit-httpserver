<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\WebSocket;

use Swoole\WebSocket\Frame;
use Rabbit\Web\RequestContext;
use Rabbit\Web\ResponseContext;
use Rabbit\Base\Helper\JsonHelper;
use Rabbit\HttpServer\Exceptions\BadRequestHttpException;
use Rabbit\HttpServer\Request;
use Rabbit\Server\ServerDispatcher;
use Rabbit\HttpServer\RouteInterface;
use Swoole\Coroutine\Http\Server;
use Swoole\Coroutine\Server as CoroutineServer;
use Throwable;

class Route implements RouteInterface
{
    protected CloseHandlerInterface $closeHandler;

    protected array $responses = [];

    public function __construct(protected ServerDispatcher $dispatcher, protected array $routes = [])
    {
    }

    public function handle(Server|CoroutineServer $server): void
    {
        foreach ($this->routes as $handShake => $route) {
            foreach ($route as $item) {
                $server->handle(
                    $item,
                    function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use (
                        $handShake,
                        $item
                    ): void {
                        try {
                            if (is_string($handShake) && ($handShake = create($handShake)) && $handShake instanceof HandShakeInterface) {
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
                                if (!JsonHelper::valid($frame->data)) {
                                    throw new BadRequestHttpException("param is not JSON");
                                }
                                $param = JsonHelper::decode($frame->data, true);
                                parse_str($request?->server['query_string'], $query);
                                $data = [
                                    'server' => $request?->server,
                                    'header' => $request?->header,
                                    'query' => $param['query'] ?? $query,
                                    'body' => $param['body'] ?? [],
                                    'content' => $request?->rawContent(),
                                    'cookie' => $request?->cookie,
                                    'files' => $request?->files,
                                    'fd' => $frame->fd,
                                    'request' => $request,
                                ];
                                $data['server']['request_uri'] = $param['cmd'] ?? $data['server']['request_uri'];
                                $psrRequest = new Request($data);
                                $psrResponse = new Response();
                                RequestContext::set($psrRequest);
                                ResponseContext::set($psrResponse);
                                $this->dispatcher->dispatch($psrRequest)->setSwooleResponse($response)->send();
                            }
                        } catch (Throwable $throw) {
                            $errorResponse = service('errorResponse', false);
                            if ($errorResponse === null) {
                                $response->push("An internal server error occurred.");
                            } else {
                                $response->push($errorResponse->handle($throw, $response));
                            }
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
