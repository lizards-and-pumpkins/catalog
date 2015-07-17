<?php

namespace Brera\Api;

class ApiRequestHandlerChain
{
    private $requestHandlers = [];

    /**
     * @param string $code
     * @param string $method
     * @param int $version
     * @param ApiRequestHandler $requestHandler
     */
    public function register($code, $method, $version, ApiRequestHandler $requestHandler)
    {
        $this->validateApiVersion($version);

        $key = $this->getRequestHandlerChainKey($code, $method, $version);
        $this->requestHandlers[$key] = $requestHandler;
    }

    /**
     * @param string $code
     * @param string $method
     * @param int $version
     * @return ApiRequestHandler
     */
    public function getApiRequestHandler($code, $method, $version)
    {
        $this->validateApiVersion($version);

        $key = $this->getRequestHandlerChainKey($code, $method, $version);

        if (!isset($this->requestHandlers[$key])) {
            return new NullApiRequestHandler;
        }

        return $this->requestHandlers[$key];
    }

    private function getRequestHandlerChainKey($code, $method, $version)
    {
        return sprintf('v%s_%s_%s', $code, $method, $version);
    }

    /**
     * @param $version
     */
    private function validateApiVersion($version)
    {
        if (!is_int($version)) {
            throw new ApiVersionMustBeIntException(
                sprintf('Api version is supposed to be an integer, got %.', gettype($version))
            );
        }
    }
}
