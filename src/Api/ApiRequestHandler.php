<?php

namespace Brera\Api;

use Brera\DefaultHttpResponse;
use Brera\Http\HttpRequestHandler;

abstract class ApiRequestHandler implements HttpRequestHandler
{
    public final function process()
    {
        $response = new DefaultHttpResponse();

        $response->addHeader('Access-Control-Allow-Origin: *');
        $response->addHeader('Access-Control-Allow-Methods: *');
        $response->addHeader('Content-Type: application/json');

        $response->setBody($this->getResponseBody());

        return $response;
    }

    /**
     * @return string
     */
    abstract protected function getResponseBody();
}
