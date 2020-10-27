<?php

declare(strict_types=1);

namespace Rabbit\HttpServer;

use Rabbit\Web\Uri;
use Rabbit\Web\MessageTrait;
use Rabbit\Web\SwooleStream;
use Rabbit\Web\UploadedFile;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\ServerRequestInterface;

class Request implements ServerRequestInterface
{
    use MessageTrait;
    /**
     * @var \Swoole\Http\Request
     */
    protected \Swoole\Http\Request $swooleRequest;

    /**
     * @var array
     */
    private array $attributes = [];

    /**
     * @var array
     */
    private array $cookieParams = [];

    /**
     * @var null|array|object
     */
    private $parsedBody;

    /**
     * the body of parser
     *
     * @var mixed
     */
    private $bodyParams;

    /**
     * @var array
     */
    private array $queryParams = [];

    /**
     * @var array
     */
    private array $serverParams = [];

    /**
     * @var array
     */
    private array $uploadedFiles = [];

    /**
     * @var string
     */
    private $method;

    /**
     * @var UriInterface
     */
    private UriInterface $uri;

    /**
     * @var string
     */
    private string $requestTarget;

    /**
     * Request constructor.
     * @param \Swoole\Http\Request $swooleRequest
     */
    public function __construct(\Swoole\Http\Request $swooleRequest)
    {
        $server = $swooleRequest->server;
        $this->method = strtoupper($server['request_method'] ?? 'GET');
        $this->setHeaders($swooleRequest->header ?? []);
        $this->uri = self::getUriFromGlobals($swooleRequest);
        $this->stream = new SwooleStream($swooleRequest->rawContent());
        $this->protocol = isset($server['server_protocol']) ? str_replace('HTTP/', '', $server['server_protocol']) : '1.1';

        $this->withCookieParams($swooleRequest->cookie ?? [])
            ->withQueryParams($swooleRequest->get ?? [])
            ->withParsedBody($swooleRequest->post ?? [])
            ->withUploadedFiles(self::normalizeFiles($swooleRequest->files ?? []))
            ->withServerParams($server ?? [])
            ->setSwooleRequest($swooleRequest);
    }

    /**
     * @param array $headers
     * @return $this
     */
    private function setHeaders(array $headers): Request
    {
        $this->headers = [];
        foreach ($headers as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }

            $value = $this->trimHeaderValues($value);
            $normalized = strtolower($header);
            $this->headers[$normalized] = $value;
        }
        return $this;
    }

    /**
     * @param \Swoole\Http\Request $swooleRequest
     * @return Uri
     */
    private static function getUriFromGlobals(\Swoole\Http\Request $swooleRequest): Uri
    {
        $server = $swooleRequest->server;
        $header = $swooleRequest->header;
        $uri = new Uri();
        $uri = $uri->withScheme(!empty($server['https']) && $server['https'] !== 'off' ? 'https' : 'http');

        $hasPort = false;
        if (isset($server['http_host'])) {
            $hostHeaderParts = explode(':', $server['http_host']);
            $uri = $uri->withHost($hostHeaderParts[0]);
            if (isset($hostHeaderParts[1])) {
                $hasPort = true;
                $uri = $uri->withPort($hostHeaderParts[1]);
            }
        } elseif (isset($server['server_name'])) {
            $uri = $uri->withHost($server['server_name']);
        } elseif (isset($server['server_addr'])) {
            $uri = $uri->withHost($server['server_addr']);
        } elseif (isset($header['host'])) {
            if (\strpos($header['host'], ':')) {
                $hasPort = true;
                list($host, $port) = explode(':', $header['host'], 2);

                if ($port !== '80') {
                    $uri = $uri->withPort($port);
                }
            } else {
                $host = $header['host'];
            }

            $uri = $uri->withHost($host);
        }

        if (!$hasPort && isset($server['server_port'])) {
            $uri = $uri->withPort($server['server_port']);
        }

        $hasQuery = false;
        if (isset($server['request_uri'])) {
            $requestUriParts = explode('?', $server['request_uri']);
            $uri = $uri->withPath($requestUriParts[0]);
            if (isset($requestUriParts[1])) {
                $hasQuery = true;
                $uri = $uri->withQuery($requestUriParts[1]);
            }
        }

        if (!$hasQuery && isset($server['query_string'])) {
            $uri = $uri->withQuery($server['query_string']);
        }

        return $uri;
    }

    /**
     * @param array $serverParams
     * @return Request
     */
    public function withServerParams(array $serverParams): Request
    {

        $this->serverParams = $serverParams;
        return $this;
    }

    /**
     * @param array $uploadedFiles
     * @return Request|static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {

        $this->uploadedFiles = $uploadedFiles;
        return $this;
    }

    /**
     * @param array|null|object $data
     * @return Request|static
     */
    public function withParsedBody($data)
    {

        $this->parsedBody = $data;
        return $this;
    }

    /**
     * @param array $query
     * @return Request|static
     */
    public function withQueryParams(array $query)
    {

        $this->queryParams = $query;
        return $this;
    }

    /**
     * @param array $cookies
     * @return Request|static
     */
    public function withCookieParams(array $cookies)
    {

        $this->cookieParams = $cookies;
        return $this;
    }

    /**
     * @param array $files
     * @return array
     */
    private static function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
            } elseif (is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);
                continue;
            } else {
                throw new \InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    /**
     * @param array $value
     * @return array|UploadedFile
     */
    private static function createUploadedFileFromSpec(array $value): UploadedFile
    {
        if (is_array($value['tmp_name'])) {
            return self::normalizeNestedFileSpec($value);
        }

        return new UploadedFile($value['tmp_name'], (int)$value['size'], (int)$value['error'], $value['name'], $value['type']);
    }

    /**
     * @param array $files
     * @return array
     */
    private static function normalizeNestedFileSpec(array $files = []): array
    {
        $normalizedFiles = [];

        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key],
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
            ];
            $normalizedFiles[$key] = self::createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * @return array|null|object
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return Request|static
     */
    public function withAttribute($name, $value)
    {

        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return $this|Request|static
     */
    public function withoutAttribute($name)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }


        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($target == '') {
            $target = '/';
        }
        if ($this->uri->getQuery() != '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    /**
     * @param mixed $requestTarget
     * @return Request|static
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new \InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }


        $this->requestTarget = $requestTarget;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return Request|static
     */
    public function withMethod($method)
    {
        $method = strtoupper($method);
        $methods = ['GET', 'POST', 'PATCH', 'PUT', 'DELETE', 'HEAD'];
        if (!in_array($method, $methods)) {
            throw new \InvalidArgumentException('Invalid Method');
        }

        $this->method = $method;
        return $this;
    }

    /**
     * @return UriInterface|Request|Uri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return $this|Request|static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($uri === $this->uri) {
            return $this;
        }


        $this->uri = $uri;

        if (!$preserveHost) {
            $this->updateHostFromUri();
        }

        return $this;
    }

    /**
     *
     */
    private function updateHostFromUri(): void
    {
        $host = $this->uri->getHost();

        if ($host === '') {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }

        if ($this->hasHeader('host')) {
            $header = $this->getHeaderLine('host');
        } else {
            $header = 'Host';
        }
        // Ensure Host is the first header.
        $this->headers = [$header => [$host]] + $this->headers;
    }

    /**
     * @return \Swoole\Http\Request
     */
    public function getSwooleRequest(): \Swoole\Http\Request
    {
        return $this->swooleRequest;
    }

    /**
     * @param \Swoole\Http\Request $swooleRequest
     * @return $this
     */
    public function setSwooleRequest(\Swoole\Http\Request $swooleRequest): Request
    {
        $this->swooleRequest = $swooleRequest;
        return $this;
    }
}
