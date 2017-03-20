<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Http\WebFront;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchFactory;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class RestApiWebFront extends WebFront
{
    final public function processRequest(): HttpResponse
    {
        try {
            $response = parent::processRequest();
            $body = $response->getBody();
            $statusCode = $response->getStatusCode();
        } catch (\Exception $e) {
            $body = json_encode(['error' => $e->getMessage()]);
            $statusCode = HttpResponse::STATUS_BAD_REQUEST;
        }

        return $this->createJsonResponse($body, $statusCode);
    }

    protected function createMasterFactory(): MasterFactory
    {
        return new CatalogMasterFactory();
    }

    final protected function registerFactories(MasterFactory $masterFactory)
    {
        $masterFactory->register(new RestApiFactory());
        $masterFactory->register(new ProductSearchFactory());
        $masterFactory->register(new UpdatingProductImportCommandFactory());
        $masterFactory->register(new UpdatingProductImageImportCommandFactory());
        $masterFactory->register(new UpdatingProductListingImportCommandFactory());
        $masterFactory->register($this->getImplementationSpecificFactory());
    }

    final protected function registerRouters(HttpRouterChain $routerChainChain)
    {
        $routerChainChain->register($this->getMasterFactory()->createApiRouter());
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
