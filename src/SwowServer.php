<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;

use Exception;
use const Swow\Errno\EMFILE;

use const Swow\Errno\ENFILE;
use const Swow\Errno\ENOMEM;
use Rabbit\Web\RequestContext;
use Swow\Http\Server\Response;
use Rabbit\Base\Core\Coroutine;
use Rabbit\Web\ResponseContext;
use Rabbit\Web\DispatcherInterface;
use Swow\Http\Server as HttpServer;
use Swow\Http\Exception as HttpException;
use Swow\Socket\Exception as SocketException;
use Swow\Coroutine\Exception as CoroutineException;

class SwowServer
{
    protected array $middlewares = [];

    public function __construct(protected DispatcherInterface $dispatcher, protected string $host = '0.0.0.0', protected int $port = 80)
    {
    }

    public function run(): void
    {
        $server = new HttpServer();
        $server->bind($this->host, $this->port)->listen();
        while (true) {
            try {
                $session = $server->acceptSession();
                $co = new Coroutine(static function (DispatcherInterface $dispatcher) use ($session): void {
                    try {
                        while (true) {
                            $request = null;
                            try {
                                $request = $session->recvHttpRequest();
                                $request->setServerParams($_SERVER);
                                $response = new Response();
                                RequestContext::set($request);
                                ResponseContext::set($response);
                                $session->sendHttpResponse($dispatcher->dispatch($request));
                            } catch (HttpException $exception) {
                                $session->error($exception->getCode(), $exception->getMessage());
                            }
                            if (!$request || !$request->getKeepAlive()) {
                                break;
                            }
                        }
                    } catch (Exception $exception) {
                        $session->error($exception->getCode(), $exception->getMessage());
                    } finally {
                        $session->close();
                    }
                }, $this->dispatcher);
                $co->resume();
            } catch (SocketException | CoroutineException $exception) {
                if (in_array($exception->getCode(), [EMFILE, ENFILE, ENOMEM], true)) {
                    sleep(1);
                } else {
                    break;
                }
            }
        }
    }
}
