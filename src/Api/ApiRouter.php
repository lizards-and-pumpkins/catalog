<?php

namespace Brera\Api;

use Brera\Http\HttpRequest;
use Brera\Http\HttpRouter;

class ApiRouter implements HttpRouter
{
    const API_URL_PREFIX = 'api';

    /**
     * @var ApiRequestHandlerChain
     */
    private $requestHandlerChain;

    public function __construct(ApiRequestHandlerChain $requestHandlerChain)
    {
        $this->requestHandlerChain = $requestHandlerChain;
    }

    /**
     * @param HttpRequest $request
     * @return ApiRequestHandler|null
     */
    public function route(HttpRequest $request)
    {
        $urlPath = trim($request->getUrlPathRelativeToWebFront(), '/');
        $urlToken = explode('/', $urlPath);

        if (self::API_URL_PREFIX !== array_shift($urlToken)) {
            return null;
        }

        $acceptHeader = $request->getHeader('Accept');
        if (!preg_match('/^application\/vnd\.brera\.\w+\.v(\d+)\+(?:json|xml)$/', $acceptHeader, $matchedVersion)) {
            return null;
        }

        $requestHandlerCode = array_shift($urlToken);
        if (!$requestHandlerCode) {
            return null;
        }

        $apiRequestHandler = $this->requestHandlerChain->getApiRequestHandler(
            strtolower($request->getMethod() . '_' . $requestHandlerCode),
            (int) $matchedVersion[1]
        );

        if ($apiRequestHandler->canProcess($request)) {
            return $apiRequestHandler;
        }

        return null;
    }
}
