<?php
/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http;

use Diagnolek\Http\Adapter\AdapterInterface;
use Diagnolek\Http\Helper\MessageBody;
use Diagnolek\Http\Adapter\Curl;
use Diagnolek\Http\Adapter\Options;
use Diagnolek\Http\Adapter\Response;
use Diagnolek\Http\Adapter\EventDispatcher;
use Diagnolek\Http\Helper\ClientHelper;
use Diagnolek\Http\Exception\ClientException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class Client implements ClientInterface, AdapterInterface
{

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    const AUTH_JWT = 'JWT';
    const AUTH_BASIC = 'BASIC';

    private $adapter;
    private $baseUrl = "";

    /**
     * @param AdapterInterface|null $adapter
     * @param Options|array|null $options
     */
    public function __construct($options = null, AdapterInterface $adapter = null, EventDispatcher $dispatcher = null)
    {
        $this->adapter = $adapter ?: new Curl();
        if ($options !== null) {
            if (is_array($options)) {
                $options = new Options($options);
            }
            if ($options instanceof Options) {
                $this->adapter->setOptions($options);
            }
        }
        if ($dispatcher !== null) {
            $this->setDispatcher($dispatcher);
        }
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $url = ClientHelper::getInstance()->uriToString($request->getUri());

        if (is_object($request->getBody()) && method_exists($request->getBody(), 'getData')) {
            $data = $request->getBody()->getData();
        } else {
            $data = [];
        }

        $headers = $request->getHeaders();

        switch (strtoupper($request->getMethod()))
        {
            case self::METHOD_POST:
                return $this->doResponse($this->post($url, $data, $headers));
            case self::METHOD_GET:
                return $this->doResponse($this->get($url, $data, $headers));
            case self::METHOD_PUT:
                return $this->doResponse($this->put($url, $data, $headers));
            case self::METHOD_DELETE:
                return $this->doResponse($this->delete($url, $data, $headers));
            default:
                return ClientHelper::getInstance()->createResponse(405, 'Method Not Allowed');
        }
    }

    /**
     * @throws ClientException
     */
    public function execWithException($method, $url, array $data = [], array $headers = []): Response
    {
        if (!in_array(strtoupper($method),[self::METHOD_POST,self::METHOD_GET,self::METHOD_PUT,self::METHOD_DELETE])) {
            throw new ClientException('Method Not Allowed', 405);
        }

        $method = strtolower($method);
        /* @var $response Response */
        $response = $this->$method($url, $data, $headers);

        if ($response->getError()) {
            throw new ClientException($response->getError(), $response->getStatus());
        }

        return $response;
    }

    public function post($url, array $data = [], array $headers = []): Response
    {
        return $this->adapter->post($this->parseUrl($url), $data, $headers);
    }

    public function get($url, array $data = [], array $headers = []): Response
    {
        return $this->adapter->get($this->parseUrl($url), $data, $headers);
    }

    public function put($url, array $data = [], array $headers = []): Response
    {
        return $this->adapter->put($this->parseUrl($url), $data, $headers);
    }

    public function delete($url, array $data = [], array $headers = []): Response
    {
        return $this->adapter->delete($this->parseUrl($url), $data, $headers);
    }

    private function doResponse(Response $result) : RequestInterface
    {
        $response = ClientHelper::getInstance()->createResponse($result->getStatus());
        $response->withBody(new MessageBody($result->getContent(), MessageBody::FROM_CONTENT));
        return $response;
    }

    private function parseUrl($url): string
    {
        if ($url instanceof UriInterface) {
            if ($url->getUserInfo()) {
                $this->adapter->getOptions()->offsetSet(Options::OPT_USERPWD, $url->getUserInfo());
            }
            $url = (string)$url;
        } else {
            $url = $this->baseUrl . $url;
        }
        return $url;
    }

    public function setOptions(Options $options): void
    {
        $this->adapter->setOptions($options);
    }

    public function getOptions(): Options
    {
        return $this->adapter->getOptions();
    }

    public function setDispatcher(EventDispatcher $dispatcher): void
    {
        $this->adapter->setDispatcher($dispatcher);
    }

    public function getDispatcher(): EventDispatcher
    {
        return $this->adapter->getDispatcher();
    }

    public function auth(string $type, string $token): Client
    {
        switch ($type)
        {
            case self::AUTH_JWT:
                $this->getOptions()->offsetSet(Options::OPT_AUTH, 'Authorization:Bearer '.$token);
                break;

            case self::AUTH_BASIC:
                $this->getOptions()->offsetSet(Options::OPT_AUTH, 'Authorization:Basic '.base64_encode($token));
                break;
        }
        return $this;
    }

    public function baseUrl(string $val): Client
    {
        $this->baseUrl = $val;
        return $this;
    }
}