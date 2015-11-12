<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Api\ApiRequestHandlerChain;
use LizardsAndPumpkins\Api\ApiRouter;
use LizardsAndPumpkins\Content\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\SimpleEuroPriceSnippetTransformation;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\GenericHttpRouter;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRouter;
use LizardsAndPumpkins\Product\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Product\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\DefaultNumberOfProductsPerPageSnippetRenderer;
use LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\Product\ProductDetailViewRequestHandler;
use LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductListingRequestHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;
use LizardsAndPumpkins\Product\MultipleProductStockQuantityApiV1PutRequestHandler;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionRequestHandler;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchRequestHandler;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\Utils\Directory;
use LizardsAndPumpkins\Context\Context;

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
     * @var SnippetKeyGeneratorLocatorStrategy
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
            'put_templates',
            $version,
            $this->getMasterFactory()->createTemplatesApiV1PutRequestHandler()
        );
    }

    /**
     * @return CatalogImportApiV1PutRequestHandler
     */
    public function createCatalogImportApiV1PutRequestHandler()
    {
        return CatalogImportApiV1PutRequestHandler::create(
            $this->getMasterFactory()->createCatalogImport(),
            $this->getCatalogImportDirectoryConfig(),
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
            $this->getMasterFactory()->createProductStockQuantitySourceBuilder()
        );
    }

    /**
     * @return TemplatesApiV1PutRequestHandler
     */
    public function createTemplatesApiV1PutRequestHandler()
    {
        return new TemplatesApiV1PutRequestHandler(
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
     * @return HttpRouter
     */
    public function createProductDetailViewRouter()
    {
        return new GenericHttpRouter($this->createProductDetailViewRequestHandler());
    }

    /**
     * @return HttpRouter
     */
    public function createProductListingRouter()
    {
        return new GenericHttpRouter($this->createProductListingRequestHandler());
    }

    /**
     * @return ProductDetailViewRequestHandler
     */
    private function createProductDetailViewRequestHandler()
    {
        return new ProductDetailViewRequestHandler(
            $this->createContext(),
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
            $this->createContext(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator(),
            $this->getMasterFactory()->getProductListingFilterNavigationAttributeCodes(),
            $this->getDefaultNumberOfProductsPerPageConfig(),
            $this->getMasterFactory()->createSearchCriteriaBuilder()
        );
    }

    /**
     * @return int
     */
    private function getDefaultNumberOfProductsPerPageConfig()
    {
        return 9;
    }

    /**
     * @return SnippetKeyGeneratorLocatorStrategy
     */
    public function createSnippetKeyGeneratorLocator()
    {
        return new CompositeSnippetKeyGeneratorLocatorStrategy(
            $this->getMasterFactory()->createContentBlockSnippetKeyGeneratorLocatorStrategy(),
            $this->getMasterFactory()->createRegistrySnippetKeyGeneratorLocatorStrategy()
        );
    }

    /**
     * @return RegistrySnippetKeyGeneratorLocatorStrategy
     */
    public function createRegistrySnippetKeyGeneratorLocatorStrategy()
    {
        $registryKeyGeneratorLocator = new RegistrySnippetKeyGeneratorLocatorStrategy;
        $registryKeyGeneratorLocator->register(
            ProductDetailViewSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            ProductInListingSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductInListingSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            ProductListingPageSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            $this->getMasterFactory()->getRegularPriceSnippetKey(),
            function () {
                return $this->getMasterFactory()->createPriceSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            $this->getMasterFactory()->getProductBackOrderAvailabilitySnippetKey(),
            function () {
                return $this->getMasterFactory()->createProductBackOrderAvailabilitySnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            DefaultNumberOfProductsPerPageSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createDefaultNumberOfProductsPerPageSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            ProductListingCriteriaSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingCriteriaSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            ProductSearchResultMetaSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductSearchResultMetaSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            ProductInSearchAutosuggestionSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductInSearchAutosuggestionSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            ProductSearchAutosuggestionMetaSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductSearchAutosuggestionMetaSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            ProductSearchAutosuggestionSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductSearchAutosuggestionSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            ProductJsonSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductJsonSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            ConfigurableProductJsonSnippetRenderer::VARIATION_ATTRIBUTES_CODE,
            function () {
                return $this->getMasterFactory()->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator();
            }
        );
        $registryKeyGeneratorLocator->register(
            ConfigurableProductJsonSnippetRenderer::ASSOCIATED_PRODUCTS_CODE,
            function () {
                return $this->getMasterFactory()->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator();
            }
        );

        return $registryKeyGeneratorLocator;
    }

    /**
     * @return SnippetKeyGeneratorLocatorStrategy
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
        $pageBuilder->registerSnippetTransformation(
            'price',
            $this->getMasterFactory()->createPriceSnippetTransformation()
        );
    }

    /**
     * @return Context
     */
    public function createContext()
    {
        /** @var ContextBuilder $contextBuilder */
        $contextBuilder = $this->getMasterFactory()->createContextBuilder();
        return $contextBuilder->createFromRequest($this->request);
    }

    /**
     * @return HttpRouter
     */
    public function createProductSearchResultRouter()
    {
        return new GenericHttpRouter($this->createProductSearchRequestHandler());
    }

    /**
     * @return ProductSearchRequestHandler
     */
    private function createProductSearchRequestHandler()
    {
        return new ProductSearchRequestHandler(
            $this->createContext(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator()
        );
    }

    /**
     * @return HttpRouter
     */
    public function createProductSearchAutosuggestionRouter()
    {
        return new GenericHttpRouter($this->createProductSearchAutosuggestionRequestHandler());
    }

    /**
     * @return ProductSearchAutosuggestionRequestHandler
     */
    private function createProductSearchAutosuggestionRequestHandler()
    {
        return new ProductSearchAutosuggestionRequestHandler(
            $this->createContext(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator()
        );
    }

    /**
     * @return SimpleEuroPriceSnippetTransformation
     */
    public function createPriceSnippetTransformation()
    {
        return new SimpleEuroPriceSnippetTransformation();
    }
}
