<?php

namespace Brera\Api;

use Brera\DefaultHttpResponse;
use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;

abstract class ApiRequestHandler implements HttpRequestHandler
{
    /**
     * @param HttpRequest $request
     * @return DefaultHttpResponse
     */
    final public function process(HttpRequest $request)
    {
        $body = $this->getResponseBody($request);
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => '*',
            'Content-Type' => 'application/json',
        ];

        return DefaultHttpResponse::create($body, $headers);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    abstract protected function getResponseBody(HttpRequest $request);
}
