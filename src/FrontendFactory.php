<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Api\ApiRequestHandlerLocator;
use LizardsAndPumpkins\Api\ApiRouter;
use LizardsAndPumpkins\Content\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductDetailViewRequestHandler;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductJsonService;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingPageContentBuilder;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingPageRequest;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandler;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationsApiV1GetRequestHandler;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationsLocator;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationsService;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationTypeCode;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\RelationType\SameSeriesProductRelations;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchAutosuggestionRequestHandler;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchRequestHandler;
use LizardsAndPumpkins\ContentDelivery\PageBuilder;
use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\PricesJsonSnippetTransformation;
use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\ProductJsonSnippetTransformation;
use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\SimpleEuroPriceSnippetTransformation;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\GenericHttpRouter;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRouter;
use LizardsAndPumpkins\Product\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Product\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\PriceSnippetRenderer;
use LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetRenderer;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\Projection\TemplatesApiV1PutRequestHandler;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\CompositeSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\RegistrySnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;
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
     * @var SnippetKeyGeneratorLocator
     */
    private $snippetKeyGeneratorLocator;

    /**
     * @return ApiRouter
     */
    public function createApiRouter()
    {
        $requestHandlerLocator = new ApiRequestHandlerLocator();
        $this->registerApiRequestHandlers($requestHandlerLocator);

        return new ApiRouter($requestHandlerLocator);
    }

    private function registerApiRequestHandlers(ApiRequestHandlerLocator $requestHandlerLocator)
    {
        $this->registerApiV1RequestHandlers($requestHandlerLocator);
    }

    private function registerApiV1RequestHandlers(ApiRequestHandlerLocator $requestHandlerLocator)
    {
        $version = 1;

        $requestHandlerLocator->register(
            'put_catalog_import',
            $version,
            $this->getMasterFactory()->createCatalogImportApiV1PutRequestHandler()
        );

        $requestHandlerLocator->register(
            'put_content_blocks',
            $version,
            $this->getMasterFactory()->createContentBlocksApiV1PutRequestHandler()
        );

        $requestHandlerLocator->register(
            'put_templates',
            $version,
            $this->getMasterFactory()->createTemplatesApiV1PutRequestHandler()
        );
        
        $requestHandlerLocator->register(
            'get_products',
            $version,
            $this->getMasterFactory()->createProductRelationsApiV1GetRequestHandler()
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
        /** @var ConfigReader $configReader */
        $configReader = $this->getMasterFactory()->createConfigReader();
        $catalogImportDirectory = $configReader->get('catalog_import_directory');

        return null === $catalogImportDirectory ?
            __DIR__ . '/../tests/shared-fixture' :
            $catalogImportDirectory;
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
    public function createProductListingRequestHandler()
    {
        return new ProductListingRequestHandler(
            $this->createContext(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createProductListingCriteriaSnippetKeyGenerator(),
            $this->getMasterFactory()->getProductListingFilterNavigationConfig(),
            $this->getMasterFactory()->createProductListingPageContentBuilder(),
            $this->getMasterFactory()->createProductListingPageRequest()
        );
    }

    /**
     * @return ProductListingPageContentBuilder
     */
    public function createProductListingPageContentBuilder()
    {
        return new ProductListingPageContentBuilder(
            $this->getMasterFactory()->createProductJsonService(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->getTranslatorRegistry(),
            ...$this->getMasterFactory()->getProductListingSortOrderConfig()
        );
    }

    /**
     * @return ProductListingPageRequest
     */
    public function createProductListingPageRequest()
    {
        return new ProductListingPageRequest(
            $this->getMasterFactory()->getProductsPerPageConfig(),
            ...$this->getMasterFactory()->getProductListingSortOrderConfig()
        );
    }

    /**
     * @return SnippetKeyGeneratorLocator
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
        $registrySnippetKeyGeneratorLocator = new RegistrySnippetKeyGeneratorLocatorStrategy;
        $registrySnippetKeyGeneratorLocator->register(
            ProductDetailViewSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductInListingSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductInListingSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingPageSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            PriceSnippetRenderer::PRICE,
            function () {
                return $this->getMasterFactory()->createPriceSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            PriceSnippetRenderer::SPECIAL_PRICE,
            function () {
                return $this->getMasterFactory()->createSpecialPriceSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingCriteriaSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingCriteriaSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductSearchResultMetaSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductSearchResultMetaSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductInSearchAutosuggestionSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductInSearchAutosuggestionSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductSearchAutosuggestionMetaSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductSearchAutosuggestionMetaSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductSearchAutosuggestionSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductSearchAutosuggestionSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductJsonSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductJsonSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ConfigurableProductJsonSnippetRenderer::VARIATION_ATTRIBUTES_CODE,
            function () {
                return $this->getMasterFactory()->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ConfigurableProductJsonSnippetRenderer::ASSOCIATED_PRODUCTS_CODE,
            function () {
                return $this->getMasterFactory()->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator();
            }
        );

        return $registrySnippetKeyGeneratorLocator;
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
        // Todo: remove when product detail page uses product json only
        $pageBuilder->registerSnippetTransformation(
            ProductJsonSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductJsonSnippetTransformation()
        );
        
        $pageBuilder->registerSnippetTransformation(
            PriceSnippetRenderer::PRICE,
            $this->getMasterFactory()->createPriceSnippetTransformation()
        );

        // Todo: remove when product detail page uses product json only
        $pageBuilder->registerSnippetTransformation(
            PriceSnippetRenderer::SPECIAL_PRICE,
            $this->getMasterFactory()->createPriceSnippetTransformation()
        );
        
        // Todo: remove when product listing page uses ProductJsonService
        $pageBuilder->registerSnippetTransformation(
            'product_prices',
            $this->getMasterFactory()->createPricesJsonSnippetTransformation()
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
    public function createProductSearchRequestHandler()
    {
        return new ProductSearchRequestHandler(
            $this->createContext(),
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createProductSearchResultMetaSnippetKeyGenerator(),
            $this->getMasterFactory()->getProductSearchResultsFilterNavigationConfig(),
            $this->getMasterFactory()->createSearchCriteriaBuilder(),
            $this->getMasterFactory()->getSearchableAttributeCodes(),
            $this->getMasterFactory()->createProductListingPageContentBuilder(),
            $this->getMasterFactory()->createProductListingPageRequest()
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
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator(),
            $this->getMasterFactory()->createSearchCriteriaBuilder(),
            $this->getMasterFactory()->getSearchableAttributeCodes(),
            $this->getMasterFactory()->getProductSearchAutosuggestionSortOrderConfig()
        );
    }

    /**
     * @return SimpleEuroPriceSnippetTransformation
     */
    public function createPriceSnippetTransformation()
    {
        return new SimpleEuroPriceSnippetTransformation();
    }

    /**
     * @return PricesJsonSnippetTransformation
     */
    public function createPricesJsonSnippetTransformation()
    {
        return new PricesJsonSnippetTransformation($this->getMasterFactory()->createPriceSnippetTransformation());
    }

    /**
     * @return ProductJsonSnippetTransformation
     */
    public function createProductJsonSnippetTransformation()
    {
        return new ProductJsonSnippetTransformation($this->getMasterFactory()->createEnrichProductJsonWithPrices());
    }

    /**
     * @return ProductRelationsService
     */
    public function createProductRelationsService()
    {
        return new ProductRelationsService(
            $this->getMasterFactory()->createProductRelationsLocator(),
            $this->getMasterFactory()->createProductJsonService(),
            $this->getMasterFactory()->createContext()
        );
    }

    /**
     * @return SameSeriesProductRelations
     */
    public function createSameSeriesProductRelations()
    {
        return new SameSeriesProductRelations(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createProductJsonSnippetKeyGenerator(),
            $this->getMasterFactory()->createContext()
        );
    }

    /**
     * @return ProductRelationsLocator
     */
    public function createProductRelationsLocator()
    {
        $productRelationsLocator = new ProductRelationsLocator();
        $productRelationsLocator->register(
            ProductRelationTypeCode::fromString('related-models'),
            [$this->getMasterFactory(), 'createSameSeriesProductRelations']
        );
        return $productRelationsLocator;
    }

    /**
     * @return ProductRelationsApiV1GetRequestHandler
     */
    public function createProductRelationsApiV1GetRequestHandler()
    {
        return new ProductRelationsApiV1GetRequestHandler(
            $this->getMasterFactory()->createProductRelationsService()
        );
    }

    /**
     * @return ProductJsonService
     */
    public function createProductJsonService()
    {
        return new ProductJsonService(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createProductJsonSnippetKeyGenerator(),
            $this->getMasterFactory()->createPriceSnippetKeyGenerator(),
            $this->getMasterFactory()->createSpecialPriceSnippetKeyGenerator(),
            $this->getMasterFactory()->createEnrichProductJsonWithPrices(),
            $this->getMasterFactory()->createContext()
        );
    }

    /**
     * @return EnrichProductJsonWithPrices
     */
    public function createEnrichProductJsonWithPrices()
    {
        return new EnrichProductJsonWithPrices(
            $this->getMasterFactory()->createContext()
        );
    }
}
