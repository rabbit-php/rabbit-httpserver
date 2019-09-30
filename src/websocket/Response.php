<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/20
 * Time: 22:48
 */

namespace rabbit\httpserver\websocket;


use Psr\Http\Message\ResponseInterface;
use rabbit\exception\NotSupportedException;
use rabbit\helper\ArrayHelper;
use rabbit\web\MessageTrait;

/**
 * Class Response
 * @package rabbit\wsserver
 */
class Response implements ResponseInterface
{
    use MessageTrait;

    const FD_LIST = 'fdList';
    /**
     * @var array
     */
    private $attributes = [];
    /**
     * @var int
     */
    private $statusCode = 200;
    /**
     * @var string
     */
    private $charset = 'utf-8';
    /** @var \Swoole\Http\Response */
    protected $swooleResponse;

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
     * @param $content
     * @return Response
     */
    public function withContent($content): Response
    {
        if ($this->stream) {
            return $this;
        }

        $this->stream = $content;
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
     * @throws \Exception
     */
    public function push(int $fd, string $msg): void
    {
        (new \Swoole\Http\Response($fd))->push($message);
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return Response
     */
    public function withCharset(string $charset): Response
    {
        $this->charset = $charset;
        return $this;
    }

}