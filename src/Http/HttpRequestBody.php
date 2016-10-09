<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

class HttpRequestBody
{
    /**
     * @var string
     */
    private $requestBody;

    public function __construct(string $requestBody)
    {
        $this->requestBody = $requestBody;
    }
    
    public function toString() : string
    {
        return $this->requestBody;
    }
}
