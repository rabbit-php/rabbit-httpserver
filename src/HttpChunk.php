<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;

/**
 * Class HttpChunk
 * @package common\Server
 */
class HttpChunk
{
    /**
     * HttpChunk constructor.
     * @param Response $response
     * @param string $preStr
     * @param string $endStr
     */
    public function __construct(private Response $response, private string $preStr, private string $endStr)
    {
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
