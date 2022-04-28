<?php
/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http\Adapter;

use Psr\EventDispatcher\StoppableEventInterface;

final class Event implements StoppableEventInterface
{
    const BEFORE_SEND_URL = 'before_send_url';
    const BEFORE_SEND_DATA = 'before_send_data';
    const BEFORE_SEND_HEADER = 'before_send_header';
    const AFTER_RESPONSE = 'after_response';
    const PARSE_COOKIE = 'parse_cookie';

    private $name;
    private $value;
    private $stop;

    public function __construct($name, $value, $stop = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->stop = $stop;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param  $value mixed
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function isPropagationStopped(): bool
    {
        return $this->stop === true;
    }
}