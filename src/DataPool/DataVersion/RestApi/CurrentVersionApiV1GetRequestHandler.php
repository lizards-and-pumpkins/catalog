<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion\RestApi;

use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\RestApi\RestApiRequestHandler;

class CurrentVersionApiV1GetRequestHandler implements RestApiRequestHandler
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    public function __construct(DataPoolReader $dataPoolReader)
    {
        $this->dataPoolReader = $dataPoolReader;
    }

    public function process(HttpRequest $request): HttpResponse
    {
        return GenericHttpResponse::create($this->getResponseBody(), $headers = [], HttpResponse::STATUS_OK);
    }

    public function canProcess(HttpRequest $request): bool
    {
        return $request->getMethod() === HttpRequest::METHOD_GET;
    }

    private function getResponseBody(): string
    {
        return json_encode([
            'data' => [
                'current_version' => $this->dataPoolReader->getCurrentDataVersion(),
                'previous_version' => $this->dataPoolReader->getPreviousDataVersion(),
            ]
        ]);
    }
}
