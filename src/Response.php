<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;

use Rabbit\Web\Cookie;
use Rabbit\Web\MessageTrait;
use Rabbit\Base\Helper\FileHelper;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Response
 * @package rabbit\httpserver
 */
class Response implements ResponseInterface
{
    use MessageTrait;

    public static array $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    private bool $_isSend = false;

    private \Swoole\Http\Response $swooleResponse;

    private string $reasonPhrase = '';

    private int $statusCode = 200;

    private string $charset = 'utf-8';

    private array $cookies = [];

    private bool $pre_send = false;

    public function withStatus($code, $reasonPhrase = '')
    {

        $this->statusCode = (int)$code;
        if (!$reasonPhrase && isset(self::$phrases[$code])) {
            $reasonPhrase = self::$phrases[$code];
        }
        $this->reasonPhrase = $reasonPhrase;
        return $this;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    public function sendHeaders(): void
    {
        // Write Headers to swoole response
        foreach ($this->getHeaders() as $key => $value) {
            if (is_array($value)) {
                $this->swooleResponse->header($key, implode(';', $value));
            } else {
                $this->swooleResponse->header($key, $value);
            }
        }
    }

    public function sendCookies(): void
    {
        foreach ((array)$this->cookies as $domain => $paths) {
            foreach ($paths ?? [] as $path => $item) {
                foreach ($item ?? [] as $name => $cookie) {
                    if ($cookie instanceof Cookie) {
                        $this->swooleResponse->cookie(
                            $cookie->getName(),
                            $cookie->getValue() ?: 1,
                            $cookie->getExpiresTime(),
                            $cookie->getPath(),
                            $cookie->getDomain(),
                            $cookie->isSecure(),
                            $cookie->isHttpOnly()
                        );
                    }
                }
            }
        }
    }

    public function send(): void
    {
        /**
         * Headers
         */
        $this->sendHeaders();

        /**
         * Cookies
         */
        $this->sendCookies();

        /**
         * Status code
         */
        $this->swooleResponse->status($this->getStatusCode());

        /**
         * Body
         */
        $this->swooleResponse->end($this->getBody()->getContents());
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function withCookie(Cookie $cookie): Response
    {

        $this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        return $this;
    }

    public function sendFile(string $filePath, string $attachmentName = null, array $options = []): void
    {
        if ($this->_isSend) {
            return;
        }
        if (!isset($options['mimeType'])) {
            $options['mimeType'] = FileHelper::getMimeTypeByExtension($filePath);
        }
        if ($attachmentName === null) {
            $attachmentName = basename($filePath);
        }
        $this->swooleResponse->header(
            'Content-disposition',
            'attachment; filename="' . urlencode($attachmentName) . '"'
        );
        $this->swooleResponse->header('Content-Type', $options['mimeType']);
        $this->swooleResponse->header('Content-Transfer-Encoding', 'binary');
        $this->swooleResponse->header('Cache-Control', 'must-revalidate');
        $this->swooleResponse->header('Pragma', 'public');
        $this->swooleResponse->sendfile($filePath);
        $this->_isSend = true;
    }

    public function sendFileContent(string $content, string $attachmentName, array $options = []): void
    {
        if ($this->_isSend) {
            return;
        }
        try {
            if (!isset($options['mimeType'])) {
                $options['mimeType'] = FileHelper::getMimeTypeByExtension($attachmentName);
            }
            $this->swooleResponse->header(
                'Content-disposition',
                'attachment; filename="' . urlencode($attachmentName) . '"'
            );
            $this->swooleResponse->header('Content-Type', $options['mimeType']);
            $this->swooleResponse->header('Content-Transfer-Encoding', 'binary');
            $this->swooleResponse->header('Cache-Control', 'must-revalidate');
            $this->swooleResponse->header('Pragma', 'public');
            $this->sendHeaders();
            $this->sendCookies();

            $len = 4096;
            while (!empty($content)) {
                $this->sendChuck(substr($content, 0, $len));
                $content = substr($content, $len);
            }
        } finally {
            $this->swooleResponse->end();
            $this->_isSend = true;
        }
    }

    public function sendChuck(string $chuck): bool
    {
        if ($this->_isSend) {
            return false;
        }
        if (!$this->pre_send) {
            $this->pre_send = true;
            $this->sendHeaders();
            $this->sendCookies();
        }
        return $this->swooleResponse->write($chuck);
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
