<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\Http\HttpRequest;

class RestApiRequestHandlerLocator
{
    const API_URL_PREFIX = 'api';

    private $requestHandlers = [];

    /**
     * @var UrlToWebsiteMap
     */
    private $urlToWebsiteMap;

    public function __construct(UrlToWebsiteMap $urlToWebsiteMap)
    {
        $this->urlToWebsiteMap = $urlToWebsiteMap;
    }

    public function register(string $code, int $version, callable $requestHandlerFactory)
    {
        $key = $this->getRequestProcessorLocatorKey($code, $version);
        $this->requestHandlers[$key] = $requestHandlerFactory;
    }

    public function getApiRequestHandler(HttpRequest $request): RestApiRequestHandler
    {
        $urlPath = trim($this->urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $request->getUrl()), '/');
        $urlToken = explode('/', $urlPath);

        if (self::API_URL_PREFIX !== array_shift($urlToken)) {
            return new NullApiRequestHandler();
        }

        if (! $request->hasHeader('Accept') || ! preg_match(
                '/^application\/vnd\.lizards-and-pumpkins\.\w+\.v(?<version>\d+)\+(?:json|xml)$/',
                $request->getHeader('Accept'),
                $matchedVersion
            )) {
            return new NullApiRequestHandler();
        }

        $requestHandlerCode = array_shift($urlToken);
        if (!$requestHandlerCode) {
            return new NullApiRequestHandler();
        }

        $code = strtolower($request->getMethod() . '_' . $requestHandlerCode);
        $version = (int) $matchedVersion['version'];

        $key = $this->getRequestProcessorLocatorKey($code, $version);

        if (!isset($this->requestHandlers[$key])) {
            return new NullApiRequestHandler();
        }

        return ($this->requestHandlers[$key])();
    }

    private function getRequestProcessorLocatorKey(string $code, int $version) : string
    {
        return sprintf('v%s_%s', $version, $code);
    }
}
