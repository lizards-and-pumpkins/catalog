<?php

namespace Brera;

use Brera\Api\ApiRequestHandlerChain;
use Brera\Api\ApiRouter;
use Brera\Content\ContentBlocksApiV1PutRequestHandler;
use Brera\Context\ContextBuilder;
use Brera\Http\HttpRequest;
use Brera\Product\CatalogImportApiV1PutRequestHandler;
use Brera\Product\DefaultNumberOfProductsPerPageSnippetRenderer;
use Brera\Product\ProductDetailViewInContextSnippetRenderer;
use Brera\Product\ProductDetailViewRequestHandler;
use Brera\Product\ProductDetailViewRouter;
use Brera\Product\ProductInListingInContextSnippetRenderer;
use Brera\Product\ProductListingMetaInfoSnippetRenderer;
use Brera\Product\ProductListingRequestHandler;
use Brera\Product\ProductListingRouter;
use Brera\Product\ProductListingSnippetRenderer;
use Brera\Product\MultipleProductStockQuantityApiV1PutRequestHandler;
use Brera\Product\ProductSearchRequestHandler;
use Brera\Product\ProductSearchResultsMetaSnippetContent;
use Brera\Product\ProductSearchResultsMetaSnippetRenderer;
use Brera\Product\ProductSearchResultsRouter;
use Brera\Utils\Directory;
use Brera\Context\Context;

class FrontendFactory implements Factory
{
    use FactoryTrait;

    /**
     * @var HttpRequest
     */
    private $request;

    public function __construct(HttpRequest $request)
    {
        $this->request = $request;
    }

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
            $this->getMasterFactory()->createProductListingSourceBuilder(),
            $this->getMasterFactory()->getLogger()
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
        return new ProductDetailViewRouter($this->createProductDetailViewRequestHandler());
    }

    /**
     * @return ProductListingRouter
     */
    public function createProductListingRouter()
    {
        return new ProductListingRouter($this->createProductListingRequestHandler());
    }

    /**
     * @return ProductDetailViewRequestHandler
     */
    private function createProductDetailViewRequestHandler()
    {
        return new ProductDetailViewRequestHandler(
            $this->getContext(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->createProductDetailPageMetaSnippetKeyGenerator()
        );
    }

    /**
     * @return ProductListingRequestHandler
     */
    private function createProductListingRequestHandler()
    {
        return new ProductListingRequestHandler(
            $this->getContext(),
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
        $snippetKeyGeneratorLocator->register(
            DefaultNumberOfProductsPerPageSnippetRenderer::CODE,
            $this->getMasterFactory()->createDefaultNumberOfProductsPerPageSnippetKeyGenerator()
        );
        $snippetKeyGeneratorLocator->register(
            ProductListingMetaInfoSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductListingMetaDataSnippetKeyGenerator()
        );
        $snippetKeyGeneratorLocator->register(
            ProductSearchResultsMetaSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductSearchResultsMetaSnippetKeyGenerator()
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
        $pageBuilder = new PageBuilder(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator(),
            $this->getMasterFactory()->getLogger()
        );
        $this->registerSnippetTransformations($pageBuilder);
        return $pageBuilder;
    }

    private function registerSnippetTransformations(PageBuilder $pageBuilder)
    {
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        /** @var ContextBuilder $contextBuilder */
        $contextBuilder = $this->getMasterFactory()->createContextBuilder();
        return $contextBuilder->createFromRequest($this->request);
    }

    /**
     * @return ProductSearchResultsRouter
     */
    public function createProductSearchResultsRouter()
    {
        return new ProductSearchResultsRouter(
            $this->createProductSearchRequestHandler()
        );
    }

    /**
     * @return ProductSearchRequestHandler
     */
    private function createProductSearchRequestHandler()
    {
        return new ProductSearchRequestHandler(
            $this->getContext(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator()
        );
    }
}
