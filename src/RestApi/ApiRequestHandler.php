<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;

abstract class ApiRequestHandler implements HttpRequestHandler
{
    final public function process(HttpRequest $request) : HttpResponse
    {
        try {
            $this->processRequest($request);
            $response = $this->getResponse($request);

            $body = $response->getBody();
            $statusCode = $response->getStatusCode();
        } catch (\Exception $e) {
            $body = json_encode(['error' => $e->getMessage()]);
            $statusCode = HttpResponse::STATUS_BAD_REQUEST;
        }

        return $this->createJsonResponse($body, $statusCode);
    }

    abstract protected function getResponse(HttpRequest $request) : HttpResponse;

    protected function processRequest(HttpRequest $request)
    {
        // Intentionally empty hook method
    }

    private function createJsonResponse(string $body, int $statusCode): HttpResponse
    {
        $headers = [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => '*',
            'Content-Type'                 => 'application/json',
        ];

        return GenericHttpResponse::create($body, $headers, $statusCode);
    }
}
