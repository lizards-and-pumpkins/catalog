<?php

namespace Brera\Http;

class HttpPostRequest extends HttpRequest
{
    /**
     * @return string
     */
    public function getMethod()
    {
        return self::METHOD_POST;
    }
}
