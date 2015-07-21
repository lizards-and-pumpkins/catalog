<?php

namespace Brera\Http;

class HttpGetRequest extends HttpRequest
{
    /**
     * @return string
     */
    final public function getMethod()
    {
        return self::METHOD_GET;
    }
}
