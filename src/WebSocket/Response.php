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

    const FD_LIST = 'fdList';
    /**
     * @var array
     */
    private array $attributes = [];
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

    /**
     * Response constructor.
     * @param \Swoole\Http\Response $response
     */
    public function __construct(\Swoole\Http\Response $response)
    {
        $this->swooleResponse = $response;
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
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * @param $name
     * @param $value
     * @return Response
     */
    public function withAttribute($name, $value): Response
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     *
     */
    public function send(): void
    {
        $fdList = ArrayHelper::getValue($this->attributes, static::FD_LIST, []);
        foreach ($fdList as $fd => $message) {
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
}
