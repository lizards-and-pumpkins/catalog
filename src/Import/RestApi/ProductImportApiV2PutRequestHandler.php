<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;

class ProductImportApiV2PutRequestHandler extends ApiRequestHandler
{

    public function canProcess(HttpRequest $request): bool
    {
        return $request->getMethod() === HttpRequest::METHOD_PUT;
    }

    public function processRequest(HttpRequest $request): HttpResponse
    {


        return $this->getResponse($request);
    }

    protected function getResponse(HttpRequest $request): HttpResponse
    {
        $headers = [];
        $body = '';

        return GenericHttpResponse::create($body, $headers, HttpResponse::STATUS_ACCEPTED);
    }
}
