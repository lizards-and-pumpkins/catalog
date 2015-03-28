<?php

namespace Brera;

use Brera\Api\ApiRequestHandlerChain;
use Brera\Api\ApiRouter;
use Brera\Product\CatalogImportApiRequestHandler;
use Brera\Product\ProductDetailViewRequestHandlerBuilder;
use Brera\Product\ProductDetailViewRouter;
use Brera\Product\ProductListingRequestHandlerBuilder;
use Brera\Product\ProductListingRouter;

class FrontendFactory implements Factory
{
    use FactoryTrait;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $snippetKeyGeneratorLocator;

    /**
     * @return ApiRouter
     */
    public function createApiRouter()
    {
        $requestHandlerChain = new ApiRequestHandlerChain();
        $this->registerApiRequestHandlers($requestHandlerChain);

        return new ApiRouter($requestHandlerChain);
    }

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

    /**
     * @return ProductDetailViewRouter
     */
    public function createProductDetailViewRouter()
    {
        return new ProductDetailViewRouter($this->createProductDetailViewRequestHandlerBuilder());
    }

    /**
     * @return ProductListingRouter
     */
    public function createProductListingRouter()
    {
        return new ProductListingRouter($this->createProductListingRequestHandlerBuilder());
    }

    /**
     * @return ProductDetailViewRequestHandlerBuilder
     */
    private function createProductDetailViewRequestHandlerBuilder()
    {
        return new ProductDetailViewRequestHandlerBuilder(
            $this->getMasterFactory()->createUrlPathKeyGenerator(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @return ProductListingRequestHandlerBuilder
     */
    private function createProductListingRequestHandlerBuilder()
    {
        return new ProductListingRequestHandlerBuilder(
            $this->getMasterFactory()->createUrlPathKeyGenerator(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @return SnippetKeyGeneratorLocator
     */
    public function createSnippetKeyGeneratorLocator()
    {
        $snippetKeyGeneratorLocator = new SnippetKeyGeneratorLocator();
        $snippetKeyGeneratorLocator->register(
            'product_detail_view',
            $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator()
        );
        $snippetKeyGeneratorLocator->register(
            'product_in_listing',
            $this->getMasterFactory()->createProductInListingSnippetKeyGenerator()
        );
        $snippetKeyGeneratorLocator->register(
            'product_listing',
            $this->getMasterFactory()->createProductListingSnippetKeyGenerator()
        );

        return $snippetKeyGeneratorLocator;
    }

    /**
     * @return SnippetKeyGeneratorLocator
     */
    public function getSnippetKeyGeneratorLocator()
    {
        if (is_null($this->snippetKeyGeneratorLocator)) {
            $this->snippetKeyGeneratorLocator = $this->createSnippetKeyGeneratorLocator();
        }
        return $this->snippetKeyGeneratorLocator;
    }
}
