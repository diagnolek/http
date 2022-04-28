<?php

/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http\Helper;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class ClientHelper implements RequestFactoryInterface, ResponseFactoryInterface, UriFactoryInterface
{
    private static $instance;

    public static function getInstance(): ClientHelper
    {
        if (self::$instance == null) {
            self::$instance = new ClientHelper();
        }
        return self::$instance;
    }

    private function __construct()
    {

    }

    public function createRequest(string $method, $uri): RequestInterface
    {
        $request = new class extends MessageAbstract implements RequestInterface {

            private $method;
            /* @var $uri Psr\Http\Message\UriInterface */
            private $uri;

            public function getRequestTarget()
            {
                // TODO: Implement getRequestTarget() method.
            }

            public function withRequestTarget($requestTarget)
            {
                // TODO: Implement withRequestTarget() method.
            }

            public function getMethod()
            {
                return $this->method;
            }

            public function withMethod($method)
            {
                $this->method = $method;
            }

            public function getUri(): UriInterface
            {
                if ($this->uri == null) {
                    $this->uri = ClientHelper::getInstance()->createUri();
                }
                return $this->uri;
            }

            public function withUri(UriInterface $uri, $preserveHost = false): void
            {
                $this->uri = $uri;
            }
        };

        if (is_string($uri)) {
            $uri = ClientHelper::getInstance()->createUri($uri);
        }

        $request->withMethod($method);
        $request->withUri($uri);

        return $request;
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = new class extends MessageAbstract implements ResponseInterface {

            private $code;
            private $reasonPhrase;

            public function getStatusCode()
            {
                return $this->code;
            }

            public function withStatus($code, $reasonPhrase = '')
            {
                $this->code = $code;
                $this->reasonPhrase = $reasonPhrase;
            }

            public function getReasonPhrase()
            {
                return $this->reasonPhrase;
            }
        };

        $response->withStatus($code, $reasonPhrase);
        return $response;
    }

    public function createUri(string $uri = ''): UriInterface
    {
        $instance = new class implements UriInterface {

            private $schema = "http";
            private $host = "";
            private $port = null;
            private $user = "";
            private $password = "";
            private $path = "";
            private $query = "";
            private $fragment = "";

            public function getScheme(): string
            {
                return $this->schema;
            }

            public function getAuthority(): string
            {
                return "{$this->user}@{$this->host}:{$this->port}";
            }

            public function getUserInfo(): string
            {
                return "{$this->user}:{$this->password}";
            }

            public function getHost(): string
            {
                return $this->host;
            }

            public function getPort():? int
            {
                return $this->port;
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function getQuery(): string
            {
                return $this->query;
            }

            public function getFragment(): string
            {
                return $this->fragment;
            }

            public function withScheme($scheme)
            {
                $this->schema = $scheme;
            }

            public function withUserInfo($user, $password = null)
            {
                $this->user = $user;
                $this->password = $password ?: "";
            }

            public function withHost($host)
            {
                $this->host = $host;
            }

            public function withPort($port)
            {
                $this->port = $port;
            }

            public function withPath($path)
            {
                $this->path = $path;
            }

            public function withQuery($query)
            {
                $this->query = $query;
            }

            public function withFragment($fragment)
            {
                $this->fragment = $fragment;
            }

            public function __toString()
            {
                return ClientHelper::getInstance()->uriToString($this);
            }
        };

        $data = parse_url($uri);
        if (!empty($data)) {
            if (isset($data['scheme'])) {
                $instance->withScheme($data['scheme']);
            }
            if (isset($data['host'])) {
                $instance->withHost($data['host']);
            }
            if (isset($data['port'])) {
                $instance->withPort($data['port']);
            }
            if (isset($data['user'])) {
                $instance->withUserInfo($data['user'], $data['pass'] ?? null);
            }
            if (isset($data['path'])) {
                $instance->withPath($data['path']);
            }
            if (isset($data['query'])) {
                $instance->withQuery($data['query']);
            }
            if (isset($data['fragment'])) {
                $instance->withFragment($data['fragment']);
            }
        }
        return $instance;
    }

    public function uriToString(UriInterface $uri): string
    {
        $str = "{$uri->getScheme()}://{$uri->getHost()}";
        $str .= $uri->getPort() != null ? ":{$uri->getPort()}" : "";
        $str .= $uri->getPath() != "" ? "/{$uri->getPath()}" : "";
        $str .= $uri->getQuery() != "" ? "?{$uri->getQuery()}" : "";
        $str .= $uri->getFragment() != "" ? "#{$uri->getFragment()}" : "";

        return $str;
    }
}