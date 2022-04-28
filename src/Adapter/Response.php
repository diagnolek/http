<?php
/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http\Adapter;

class Response
{

    protected $content;
    protected $status;
    protected $error;
    protected $headers;

    public function __construct($content, $status, $error, $headers = null)
    {
        $this->content = $content;
        $this->status = $status;
        $this->error = $error;
        $this->headers = $headers;
    }

    public function getContent():? string
    {
        return $this->content;
    }

    public function getStatus():? int
    {
        return $this->status;
    }


    public function getError():? string
    {
        return $this->error;
    }

    public function getHeaders():? array
    {
        return $this->headers;
    }



}