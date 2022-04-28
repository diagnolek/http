<?php
/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http\Helper;

use Psr\Http\Message\StreamInterface;

class MessageBody implements StreamInterface
{
    const FROM_CONTENT = 'content';
    const FROM_FILE = 'file';
    const FROM_DATA = 'data';

    protected $content = "";
    protected $file = null;
    protected $metadata = [];
    protected $data = [];

    public function __construct($body = "", $from = "", array $metadata = [])
    {
        switch ($from)
        {
            case self::FROM_CONTENT;
                $this->content = $body;
                break;

            case self::FROM_DATA;
                if (!is_array($body)) {
                    throw new \RuntimeException("invalid format body");
                }
                $this->data = $body;
                break;

            case self::FROM_FILE;
            if (!file_exists($body)) {
                throw new \RuntimeException("$body file not exists");
            }
            $this->file = ['handler' => fopen($body, 'r'), 'filename' => $body];
            break;
        }
        $this->metadata = $metadata;
    }

    public function __destruct()
    {
        if ($this->file) {
            fclose($this->file['handler']);
        }
    }

    public function __toString()
    {
        return $this->content;
    }

    public function close()
    {
        if ($this->file) {
            fclose($this->file['handler']);
        }
    }

    public function detach()
    {

    }

    public function getSize(): int
    {
        if ($this->file) {
            return filesize($this->file['filename']);
        }
       return 0;
    }

    public function tell(): int
    {
        if ($this->file) {
            return ftell($this->file['handler']);
        }
        return -1;
    }

    public function eof(): bool
    {
        return $this->tell() == $this->getSize();
    }

    public function isSeekable(): bool
    {
        return $this->file !== null;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if ($this->file) {
            fseek($this->file['handler'], $offset, $whence);
        }
    }

    public function rewind()
    {
        if ($this->file) {
            fseek($this->file['handler'], 0);
        }
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string)
    {

    }

    public function isReadable(): bool
    {
        return $this->file !== null;
    }

    public function read($length): string
    {
        if ($this->file) {
            return fread($this->file, $length);
        }
        return "";
    }

    public function getContents(): string
    {
        return $this->content;
    }

    public function getMetadata($key = null)
    {
        return $this->metadata[$key] ?? null;
    }

    public function getData(): array
    {
        return $this->data;
    }
}