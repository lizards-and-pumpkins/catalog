<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

class HttpConnectRequest extends HttpRequest
{
    public function getMethod(): string
    {
        return self::METHOD_CONNECT;
    }
}
