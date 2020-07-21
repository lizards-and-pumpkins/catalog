<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockServiceFactory;
use LizardsAndPumpkins\Http\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpFactory;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Http\WebFront;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiFactory;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;

class RestApiWebFront extends WebFront
{
    final public function processRequest(): HttpResponse
    {
        try {
            $response = parent::processRequest();
            $body = $response->getBody();
            $headers = $response->getHeaders()->getAll();
            $statusCode = $response->getStatusCode();
        } catch (\Exception $e) {
            $body = json_encode(['error' => $e->getMessage()]);
            $statusCode = HttpResponse::STATUS_BAD_REQUEST;
            $headers = [];
        }

        return $this->createJsonResponse($body, $headers, $statusCode);
    }

    protected function createMasterFactory(): MasterFactory
    {
        return new CatalogMasterFactory();
    }

    protected function registerFactories(MasterFactory $masterFactory): void
    {
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new HttpFactory());
        $masterFactory->register(new RestApiFactory());
        $masterFactory->register(new ContentBlockServiceFactory());
        $masterFactory->register(new ProductSearchApiFactory());
        $masterFactory->register(new UpdatingProductImportCommandFactory());
        $masterFactory->register(new UpdatingProductImageImportCommandFactory());
        $masterFactory->register(new UpdatingProductListingImportCommandFactory());
        $masterFactory->register($this->getImplementationSpecificFactory());
    }

    final protected function registerRouters(HttpRouterChain $routerChainChain): void
    {
        $routerChainChain->register($this->getMasterFactory()->createApiRouter());
    }

    /**
     * @param string $body
     * @param string[] $headers
     * @param int $statusCode
     * @return HttpResponse
     */
    private function createJsonResponse(string $body, array $headers, int $statusCode): HttpResponse
    {
        $corsHeaders = [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => '*',
            'Content-Type'                 => 'application/json',
        ];

        return GenericHttpResponse::create($body, array_merge($headers, $corsHeaders), $statusCode);
    }
}
