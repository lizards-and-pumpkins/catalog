<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

class WebRequestHandlerLocator
{
    private $registry = [];

    /**
     * @var callable
     */
    private $defaultCallback;

    public function __construct(callable $defaultCallback)
    {
        $this->defaultCallback = $defaultCallback;
    }

    public function register(string $requestHandlerCode, callable $callback)
    {
        $this->registry[$requestHandlerCode] = $callback;
    }

    public function getRequestHandlerForCode(string $requestHandlerCode, string $metaJson): HttpRequestHandler
    {
        return call_user_func($this->registry[$requestHandlerCode] ?? $this->defaultCallback, $metaJson);
    }
}
