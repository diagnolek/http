<?php
/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http\Adapter;

interface AdapterInterface
{
    public function setOptions(Options $options): void;

    public function getOptions(): Options;

    public function setDispatcher(EventDispatcher $dispatcher): void;

    public function getDispatcher(): EventDispatcher;

    public function post($url, array $data, array $headers): Response;

    public function get($url, array $data, array $headers): Response;

    public function put($url, array $data, array $headers): Response;

    public function delete($url, array $data, array $headers): Response;

}