<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

class HttpGetRequest extends HttpRequest
{
    final public function getMethod(): string
    {
        return self::METHOD_GET;
    }
}
