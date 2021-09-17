<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\WebSocket;

use Exception;
use Rabbit\Web\MessageTrait;
use Psr\Http\Message\ResponseInterface;
use Rabbit\Base\Exception\NotSupportedException;

/**
 * Class Response
 * @package Rabbit\HttpServer\WebSocket
 */
class Response implements ResponseInterface
{
    use MessageTrait;

    private int $statusCode = 200;

    private string $charset = 'utf-8';

    protected \Swoole\Http\Response $swooleResponse;

    protected array $fdList = [];

    public function withFdList(array $list): self
    {
        $this->fdList = $list;
        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $this->statusCode = (int)$code;
        return $this;
    }

    public function getReasonPhrase()
    {
        throw new NotSupportedException("can not call " . __METHOD__);
    }

    public function send(): void
    {
        foreach ($this->fdList as $fd => $message) {
            rgo(function () use ($fd, $message) {
                (new \Swoole\Http\Response($fd))->push($message);
            });
        }
        rgo(function () {
            $this->swooleResponse->push($this->stream);
        });
    }

    public function push(int $fd, string $msg): void
    {
        (new \Swoole\Http\Response($fd))->push($msg);
    }

    public function getSwooleResponse(): \Swoole\Http\Response
    {
        return $this->swooleResponse;
    }

    public function setSwooleResponse(\Swoole\Http\Response $response): self
    {
        $this->swooleResponse = $response;
        return $this;
    }
}
