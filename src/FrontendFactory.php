<?php

namespace Brera;

use Brera\Api\ApiRequestHandlerChain;
use Brera\Api\ApiRouter;
use Brera\Content\ContentBlocksApiV1PutRequestHandler;
use Brera\Product\CatalogImportApiV1PutRequestHandler;
use Brera\Product\ProductDetailViewInContextSnippetRenderer;
use Brera\Product\ProductDetailViewRequestHandlerBuilder;
use Brera\Product\ProductDetailViewRouter;
use Brera\Product\ProductInListingInContextSnippetRenderer;
use Brera\Product\ProductListingRequestHandlerBuilder;
use Brera\Product\ProductListingRouter;
use Brera\Product\ProductListingSnippetRenderer;
use Brera\Product\MultipleProductStockQuantityApiV1PutRequestHandler;
use Brera\Utils\Directory;

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

    private function registerApiRequestHandlers(ApiRequestHandlerChain $requestHandlerChain)
    {
        $this->registerApiV1RequestHandlers($requestHandlerChain);
    }

    private function registerApiV1RequestHandlers(ApiRequestHandlerChain $requestHandlerChain)
    {
        $version = 1;

        $requestHandlerChain->register(
            'put_catalog_import',
            $version,
            $this->getMasterFactory()->createCatalogImportApiV1PutRequestHandler()
        );

        $requestHandlerChain->register(
            'put_content_blocks',
            $version,
            $this->getMasterFactory()->createContentBlocksApiV1PutRequestHandler()
        );

        $requestHandlerChain->register(
            'put_multiple_product_stock_quantity',
            $version,
            $this->getMasterFactory()->createMultipleProductStockQuantityApiV1PutRequestHandler()
        );

        $requestHandlerChain->register(
            'put_page_templates',
            $version,
            $this->getMasterFactory()->createPageTemplatesApiV1PutRequestHandler()
        );
    }

    /**
     * @return CatalogImportApiV1PutRequestHandler
     */
    public function createCatalogImportApiV1PutRequestHandler()
    {
        return CatalogImportApiV1PutRequestHandler::create(
            $this->getMasterFactory()->getCommandQueue(),
            $this->getCatalogImportDirectoryConfig(),
            $this->getMasterFactory()->createProductSourceBuilder(),
            $this->getMasterFactory()->createProductListingSourceBuilder()
        );
    }

    /**
     * @return ContentBlocksApiV1PutRequestHandler
     */
    public function createContentBlocksApiV1PutRequestHandler()
    {
        return new ContentBlocksApiV1PutRequestHandler(
            $this->getMasterFactory()->getCommandQueue()
        );
    }

    /**
     * @return MultipleProductStockQuantityApiV1PutRequestHandler
     */
    public function createMultipleProductStockQuantityApiV1PutRequestHandler()
    {
        return MultipleProductStockQuantityApiV1PutRequestHandler::create(
            $this->getMasterFactory()->getCommandQueue(),
            Directory::fromPath($this->getCatalogImportDirectoryConfig()),
            $this->getMasterFactory()->getProductStockQuantitySourceBuilder()
        );
    }

    /**
     * @return PageTemplatesApiV1PutRequestHandler
     */
    public function createPageTemplatesApiV1PutRequestHandler()
    {
        return new PageTemplatesApiV1PutRequestHandler(
            $this->getMasterFactory()->createRootSnippetSourceListBuilder(),
            $this->getMasterFactory()->getEventQueue()
        );
    }

    /**
     * @return string
     */
    private function getCatalogImportDirectoryConfig()
    {
        return __DIR__ . '/../tests/shared-fixture';
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
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createPageBuilder()
        );
    }

    /**
     * @return ProductListingRequestHandlerBuilder
     */
    private function createProductListingRequestHandlerBuilder()
    {
        return new ProductListingRequestHandlerBuilder(
            $this->getMasterFactory()->createUrlPathKeyGenerator(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator()
        );
    }

    /**
     * @return SnippetKeyGeneratorLocator
     */
    public function createSnippetKeyGeneratorLocator()
    {
        $snippetKeyGeneratorLocator = new SnippetKeyGeneratorLocator();
        $snippetKeyGeneratorLocator->register(
            ProductDetailViewInContextSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator()
        );
        $snippetKeyGeneratorLocator->register(
            ProductInListingInContextSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductInListingSnippetKeyGenerator()
        );
        $snippetKeyGeneratorLocator->register(
            ProductListingSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductListingSnippetKeyGenerator()
        );
        $snippetKeyGeneratorLocator->register(
            $this->getMasterFactory()->getRegularPriceSnippetKey(),
            $this->getMasterFactory()->createPriceSnippetKeyGenerator()
        );
        $snippetKeyGeneratorLocator->register(
            $this->getMasterFactory()->getProductBackOrderAvailabilitySnippetKey(),
            $this->getMasterFactory()->createProductBackOrderAvailabilitySnippetKeyGenerator()
        );
        $snippetKeyGeneratorLocator->register(
            $this->getMasterFactory()->getContentBlockSnippetKey(),
            $this->getMasterFactory()->createContentBlockSnippetKeyGenerator()
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

    /**
     * @return PageBuilder
     */
    public function createPageBuilder()
    {
        return new PageBuilder(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator(),
            $this->getMasterFactory()->getLogger()
        );
    }
}
