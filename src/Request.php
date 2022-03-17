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
use Rabbit\Web\AttributeEnum;

class Request implements ServerRequestInterface
{
    use MessageTrait;

    protected array $attributes = [];

    protected array $cookieParams = [];

    protected array $parsedBody = [];

    protected array $queryParams = [];

    protected array $serverParams = [];

    protected array $uploadedFiles = [];

    protected string $method = 'GET';

    protected UriInterface $uri;

    protected ?string $requestTarget = null;

    public function __construct(array $data)
    {
        $server = $data['server'] ?? [];
        $header = $data['header'] ?? [];
        $content = $data['content'] ?? '';
        $this->method = strtoupper($server['request_method'] ?? 'GET');
        $this->setHeaders($header);
        $this->uri = self::getUriFromGlobals($server, $header);
        $this->stream = new SwooleStream($content);
        $this->protocol = isset($server['server_protocol']) ? str_replace('HTTP/', '', $server['server_protocol']) : '1.1';

        $req = $this->withCookieParams($data['cookie'] ?? [])
            ->withQueryParams($data['query'] ?? [])
            ->withParsedBody($data['body'] ?? [])
            ->withUploadedFiles(self::normalizeFiles($data['files'] ?? []))
            ->withServerParams($server ?? [])
            ->withAttribute(AttributeEnum::CONNECT_FD, $data['fd'] ?? null);
        if ($data['request'] ?? false) {
            $req->setSwooleRequest($data['request']);
        }
    }

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

    private static function getUriFromGlobals(array $server, array $header): Uri
    {
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
                    $uri = $uri->withPort((int)$port);
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

    public function withServerParams(array $serverParams): Request
    {

        $this->serverParams = $serverParams;
        return $this;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {

        $this->uploadedFiles = $uploadedFiles;
        return $this;
    }

    public function withParsedBody($data)
    {

        $this->parsedBody = $data;
        return $this;
    }

    public function withQueryParams(array $query)
    {

        $this->queryParams = $query;
        return $this;
    }

    public function withCookieParams(array $cookies)
    {

        $this->cookieParams = $cookies;
        return $this;
    }

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

    private static function createUploadedFileFromSpec(array $value): UploadedFile
    {
        if (is_array($value['tmp_name'])) {
            return self::normalizeNestedFileSpec($value);
        }

        return new UploadedFile($value['tmp_name'], (int)$value['size'], (int)$value['error'], $value['name'], $value['type']);
    }

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

    public function getServerParams()
    {
        return $this->serverParams;
    }

    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    public function withAttribute($name, $value)
    {

        $this->attributes[$name] = $value;
        return $this;
    }

    public function withoutAttribute($name)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }


        unset($this->attributes[$name]);

        return $this;
    }

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

    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new \InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }


        $this->requestTarget = $requestTarget;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

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

    public function getUri()
    {
        return $this->uri;
    }

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

    public function getSwooleRequest(): \Swoole\Http\Request
    {
        return $this->swooleRequest;
    }

    public function setSwooleRequest(\Swoole\Http\Request $swooleRequest): Request
    {
        $this->swooleRequest = $swooleRequest;
        return $this;
    }
}
