<?php

namespace Brera;

use Brera\Api\ApiRequestHandlerChain;
use Brera\Api\ApiRouter;
use Brera\Product\CatalogImportApiRequestHandler;
use Brera\Product\ProductId;
use Brera\Product\ProductSeoUrlRouter;
use Brera\Product\ProductDetailHtmlPage;

class FrontendFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return ProductSeoUrlRouter
     */
    public function createProductSeoUrlRouter()
    {
        return new ProductSeoUrlRouter(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()
        );
    }

    /**
     * @param ProductId $productId
     * @return ProductDetailHtmlPage
     */
    public function createProductDetailPage(ProductId $productId)
    {
        return new ProductDetailHtmlPage(
            $productId,
            $this->getMasterFactory()->createDataPoolReader()
        );
    }

    /**
     * @return ApiRouter
     */
    public function createApiRouter()
    {
        $requestHandlerChain = new ApiRequestHandlerChain();
        $this->registerApiRequestHandlers($requestHandlerChain);

        return new ApiRouter($requestHandlerChain);
    }

    /**
     * @param ApiRequestHandlerChain $requestHandlerChain
     */
    protected function registerApiRequestHandlers(ApiRequestHandlerChain $requestHandlerChain)
    {
        $requestHandlerChain->register(
            'catalog_import',
            $this->getMasterFactory()->createCatalogImportApiRequestHandler()
        );
    }

    /**
     * @return CatalogImportApiRequestHandler
     */
    public function createCatalogImportApiRequestHandler()
    {
        return new CatalogImportApiRequestHandler();
    }
}
