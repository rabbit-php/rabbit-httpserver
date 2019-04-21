<?php


namespace rabbit\httpserver;


/**
 * Class HttpChunk
 * @package common\Server
 */
class HttpChunk
{
    /** @var Response */
    private $response;
    /** @var string */
    private $preStr = '';
    /** @var string */
    private $endStr = '';

    /**
     * HttpChunk constructor.
     * @param Response $response
     * @param string $preStr
     * @param string $endStr
     */
    public function __construct(Response $response, string $preStr, string $endStr)
    {
        $this->response = $response;
        $this->preStr = $preStr;
        $this->endStr = $endStr;
        $this->write($preStr);
    }

    /**
     * @param string $chunk
     * @return bool
     */
    public function write(string $chunk): bool
    {
        return $this->response->sendChuck($chunk);
    }

    /**
     * @return bool
     */
    public function endSend(): bool
    {
        return $this->write($this->endStr);
    }
}