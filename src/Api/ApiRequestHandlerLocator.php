<?php

namespace LizardsAndPumpkins\Api;

use LizardsAndPumpkins\Api\Exception\ApiVersionMustBeIntException;

class ApiRequestHandlerLocator
{
    private $requestHandlers = [];

    /**
     * @param string $code
     * @param int $version
     * @param ApiRequestHandler $requestHandler
     */
    public function register($code, $version, ApiRequestHandler $requestHandler)
    {
        $this->validateApiVersion($version);

        $key = $this->getRequestProcessorLocatorKey($code, $version);
        $this->requestHandlers[$key] = $requestHandler;
    }

    /**
     * @param string $code
     * @param int $version
     * @return ApiRequestHandler
     */
    public function getApiRequestHandler($code, $version)
    {
        $this->validateApiVersion($version);

        $key = $this->getRequestProcessorLocatorKey($code, $version);

        if (!isset($this->requestHandlers[$key])) {
            return new NullApiRequestHandler;
        }

        return $this->requestHandlers[$key];
    }

    /**
     * @param string $code
     * @param string $version
     * @return string
     */
    private function getRequestProcessorLocatorKey($code, $version)
    {
        return sprintf('v%s_%s', $version, $code);
    }

    /**
     * @param int $version
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
