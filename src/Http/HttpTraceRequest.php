<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

class HttpTraceRequest extends HttpRequest
{
    public function getMethod(): string
    {
        return self::METHOD_TRACE;
    }
}
