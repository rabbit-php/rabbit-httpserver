<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\WebSocket;

use Exception;
use Rabbit\Web\MessageTrait;
use Rabbit\Base\Helper\ArrayHelper;
use Psr\Http\Message\ResponseInterface;
use Rabbit\Base\Exception\NotSupportedException;

/**
 * Class Response
 * @package Rabbit\HttpServer\WebSocket
 */
class Response implements ResponseInterface
{
    use MessageTrait;
    /**
     * @var int
     */
    private int $statusCode = 200;
    /**
     * @var string
     */
    private string $charset = 'utf-8';
    /** @var \Swoole\Http\Response */
    protected \Swoole\Http\Response $swooleResponse;

    protected array $fdList = [];

    public function withFdList(array $list): self
    {
        $this->fdList = $list;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return mixed|static
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->statusCode = (int)$code;
        return $this;
    }

    /**
     * @return string|void
     * @throws NotSupportedException
     */
    public function getReasonPhrase()
    {
        throw new NotSupportedException("can not call " . __METHOD__);
    }

    /**
     *
     */
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

    /**
     * @param int $fd
     * @param string $msg
     * @throws Exception
     */
    public function push(int $fd, string $msg): void
    {
        (new \Swoole\Http\Response($fd))->push($msg);
    }

    /**
     * @return \Swoole\Http\Response
     */
    public function getSwooleResponse(): \Swoole\Http\Response
    {
        return $this->swooleResponse;
    }

    /**
     * @param \Swoole\Http\Response $response
     * @return Response
     */
    public function setSwooleResponse(\Swoole\Http\Response $response): self
    {
        $this->swooleResponse = $response;
        return $this;
    }
}
