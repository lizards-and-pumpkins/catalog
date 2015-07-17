<?php

namespace Brera\Api;

use Brera\DefaultHttpResponse;
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
        try {
            $this->processRequest($request);
            $body = $this->getResponseBody($request);
        } catch (\Exception $e) {
            /* TODO: Implement error handling */
            throw $e;
        }

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

    /**
     * @param HttpRequest $request
     * @return void
     */
    abstract protected function processRequest(HttpRequest $request);
}
