<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

class HttpUnknownMethodRequest extends HttpRequest
{
    private $methodCode = '';
    
    public function getMethod(): string
    {
        return $this->methodCode;
    }

    public function setMethodCode(string $requestMethodCode)
    {
        $this->methodCode = strtoupper($requestMethodCode);
    }
}
