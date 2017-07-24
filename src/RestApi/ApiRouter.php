<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\Routing\HttpRouter;

class ApiRouter implements HttpRouter
{
    const API_URL_PREFIX = 'api';

    /**
     * @var ApiRequestHandlerLocator
     */
    private $requestHandlerLocator;

    /**
     * @var UrlToWebsiteMap
     */
    private $urlToWebsiteMap;

    public function __construct(ApiRequestHandlerLocator $requestHandlerLocator, UrlToWebsiteMap $urlToWebsiteMap)
    {
        $this->requestHandlerLocator = $requestHandlerLocator;
        $this->urlToWebsiteMap = $urlToWebsiteMap;
    }

    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request)
    {
        $urlPath = trim($this->urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $request->getUrl()), '/');
        $urlToken = explode('/', $urlPath);

        if (self::API_URL_PREFIX !== array_shift($urlToken)) {
            return null;
        }

        if (! $request->hasHeader('Accept') || ! preg_match(
            '/^application\/vnd\.lizards-and-pumpkins\.\w+\.v(?<version>\d+)\+(?:json|xml)$/',
            $request->getHeader('Accept'),
            $matchedVersion
        )) {
            return null;
        }

        $requestHandlerCode = array_shift($urlToken);
        if (!$requestHandlerCode) {
            return null;
        }

        $requestHandler = $this->requestHandlerLocator->getApiRequestHandler(
            strtolower($request->getMethod() . '_' . $requestHandlerCode),
            (int) $matchedVersion['version']
        );

        if ($requestHandler->canProcess($request)) {
            return $requestHandler;
        }

        return null;
    }
}
