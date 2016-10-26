<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

class ApiRequestHandlerLocator
{
    private $requestHandlers = [];

    public function register(string $code, int $version, ApiRequestHandler $requestHandler)
    {
        $key = $this->getRequestProcessorLocatorKey($code, $version);
        $this->requestHandlers[$key] = $requestHandler;
    }

    public function getApiRequestHandler(string $code, int $version) : ApiRequestHandler
    {
        $key = $this->getRequestProcessorLocatorKey($code, $version);

        if (!isset($this->requestHandlers[$key])) {
            return new NullApiRequestHandler;
        }

        return $this->requestHandlers[$key];
    }

    private function getRequestProcessorLocatorKey(string $code, int $version) : string
    {
        return sprintf('v%s_%s', $version, $code);
    }
}
