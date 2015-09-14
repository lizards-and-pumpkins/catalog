<?php

namespace LizardsAndPumpkins\Http;

class HttpPostRequest extends HttpRequest
{
    /**
     * @return string
     */
    final public function getMethod()
    {
        return self::METHOD_POST;
    }
}
