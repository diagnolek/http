<?php


/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http\Adapter;

class Curl implements AdapterInterface
{

    protected $handler;
    protected $dispatcher;
    /* @var $options Options */
    protected $options;
    protected $cookies = [];

    public function __construct()
    {
        $this->handler = curl_init();
        $this->dispatcher = new EventDispatcher();
    }

    public function __destruct()
    {
        curl_close($this->handler);
    }

    public function post($url, array $data, array $headers): Response
    {
        $this->doData($data);
        $this->doUrl($url);
        curl_setopt($this->handler, CURLOPT_POST, true);
        $this->doHeaders($headers);
        return $this->doReturn();
    }

    public function get($url, array $data, array $headers): Response
    {
        $event = $this->dispatcher->dispatch(new Event(Event::BEFORE_SEND_DATA, $data));
        if(!empty($event->getValue()) && (is_array($event->getValue()) || is_object($event->getValue()))) {
            $url .= (strpos($url, '?') === false ? '?' : ''). http_build_query($event->getValue());
        }
        $this->doUrl($url);
        curl_setopt($this->handler, CURLOPT_POST, false);
        $this->doHeaders($headers);
        return $this->doReturn();
    }

    public function put($url, array $data, array $headers): Response
    {
        $this->doData($data);
        $this->doUrl($url);
        curl_setopt($this->handler, CURLOPT_CUSTOMREQUEST, "PUT");
        $this->doHeaders($headers);
        return $this->doReturn();
    }

    public function delete($url, array $data, array $headers): Response
    {
        $this->doData($data);
        $this->doUrl($url);
        curl_setopt($this->handler, CURLOPT_CUSTOMREQUEST, "DELETE");
        $this->doHeaders($headers);
        return $this->doReturn();
    }

    protected function doUrl($url)
    {
        $event = $this->dispatcher->dispatch(new Event(Event::BEFORE_SEND_URL, $url));
        if (!filter_var($event->getValue(), FILTER_VALIDATE_URL)) {
            throw new \RuntimeException("invalid format url");
        }
        curl_setopt($this->handler, CURLOPT_URL, $event->getValue());
    }

    protected function doData($data)
    {
        $event = $this->dispatcher->dispatch(new Event(Event::BEFORE_SEND_DATA, $data));
        if (!(is_array($event->getValue()) || is_object($event->getValue()))) {
            throw new \RuntimeException("invalid format data, need array or object");
        }
        if ($this->getOptions()->isJson()) {
            curl_setopt($this->handler, CURLOPT_POSTFIELDS, json_encode($event->getValue()));
        } else {
            curl_setopt($this->handler, CURLOPT_POSTFIELDS, http_build_query($event->getValue()));
        }
    }

    private function doHeadersExt($arr) {
        if ($this->getOptions()->isJson()) {
            $arr[] = 'Content-Type: application/json';
        }
        if ($auth = $this->getOptions()->getAuth()) {
            $arr[] = $auth;
        }
        curl_setopt($this->handler, CURLOPT_HTTPHEADER, $arr);
    }

    protected function doHeaders($headers)
    {
        $event = $this->dispatcher->dispatch(new Event(Event::BEFORE_SEND_HEADER, $headers));
        if (!empty($event->getValue()) && is_array($event->getValue())) {
            $arr = $event->getValue();
            $this->doHeadersExt($arr);
        } else if ($this->getOptions()->isJson() || $this->getOptions()->getAuth()) {
            $arr = [];
            $this->doHeadersExt($arr);
        }
        if ($this->getOptions()->isHeaderResponse()) {
            curl_setopt($this->handler, CURLOPT_HEADER, 1);
        }
        if (!empty($this->cookies)) {
            curl_setopt($this->handler, CURLOPT_COOKIE, implode('; ', $this->cookies));
        }
    }

    protected function doReturn(): Response
    {
        $this->doOptions();
        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($this->handler);
        $status = curl_getinfo($this->handler, CURLINFO_HTTP_CODE);
        $error = $content === false ? curl_error($this->handler) : null;
        $headers = null;

        if ($content && $this->getOptions()->isHeaderResponse()) {
            extract($this->parseContentOfCookie($content));
        }

        $event = $this->dispatcher->dispatch(new Event(Event::AFTER_RESPONSE, compact(['content', 'status', 'error', 'headers'])));
        if (!is_array($event->getValue()) || array_diff_key(array_flip(['content', 'status', 'error', 'headers']), $event->getValue())) {
            throw new \RuntimeException("invalid format response data");
        }

        return new Response($event->getValue()['content'], $event->getValue()['status'], $event->getValue()['error'], $event->getValue()['headers']);
    }

    protected function parseContentOfCookie($body): array
    {
        $lines = explode("\n", $body);
        $content = "";
        $cookies = null;
        $headers = null;
        foreach($lines as $num => $line){
            $l = str_replace("\r", "", $line);
            if(trim($l) == ""){
                $headers = array_slice($lines, 0, $num);
                for ($i = $num + 1; $i < count($lines); $i++) {
                    $content .= $lines[$i];
                }
                $cookies = preg_grep('/^Set-Cookie:/', $headers);
                break;
            }
        }
        $event = $this->dispatcher->dispatch(new Event(Event::PARSE_COOKIE, $cookies));
        $cookies = $event->getValue();
        if (!empty($cookies) && is_array($cookies)) {
            $a = str_replace('Set-Cookie:', '', array_shift($cookies) );
            foreach (explode(";", $a) as $b) {
                list($name, $value) = explode('=', $b);
                if($name && $value) {
                    $this->cookies[trim($name)] = trim($b);
                }
            }
        }
        return compact('content', 'headers');
    }

    protected function doOptions()
    {
        if (!$this->getOptions()->isVerify()) {
            curl_setopt($this->handler, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->handler, CURLOPT_SSL_VERIFYPEER, false);
        }
        $options = $this->getOptions()->getArrayCopy();
        if (!empty($options)) {
            curl_setopt_array($this->handler, $options);
        }
    }

    public function setOptions(Options $options): void
    {
        $this->options = $options;
    }

    public function getOptions(): Options
    {
        if ($this->options === null) {
            $this->options = new Options();
        }
        return $this->options;
    }

    public function setDispatcher(EventDispatcher $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function getDispatcher(): EventDispatcher
    {
        return $this->dispatcher;
    }
}