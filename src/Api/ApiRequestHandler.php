<?php

namespace Brera\Api;

use Brera\DefaultHttpResponse;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;

abstract class ApiRequestHandler implements HttpRequestHandler
{
    public final function process(HttpRequest $request)
    {
        $response = new DefaultHttpResponse();

        $response->addHeader('Access-Control-Allow-Origin: *');
        $response->addHeader('Access-Control-Allow-Methods: *');
        $response->addHeader('Content-Type: application/json');

        $response->setBody($this->getResponseBody($request));

        return $response;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    abstract protected function getResponseBody(HttpRequest $request);
}
