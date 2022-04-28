<?php
/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http\Helper;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class MessageAbstract implements MessageInterface
{
    protected $protocolVersion = "1.1";
    protected $headers = [];
    protected $body = null;

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version)
    {
        $this->protocolVersion = $version;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return key_exists($name, $this->headers);
    }

    public function getHeader($name): string
    {
       return $this->headers[$name] ?? "";
    }

    public function getHeaderLine($name)
    {
        // TODO: Implement getHeaderLine() method.
    }

    public function withHeader($name, $value)
    {
       $this->headers[$name] = $value;
    }

    public function withAddedHeader($name, $value)
    {
        $this->withHeader($name, $value);
    }

    public function withoutHeader($name)
    {
        unset($this->headers[$name]);
    }

    public function getBody(): StreamInterface
    {
        if ($this->body === null) {
            $this->body = new MessageBody();
        }
        return $this->body;
    }

    public function withBody(StreamInterface $body)
    {
        $this->body = $body;
    }
}