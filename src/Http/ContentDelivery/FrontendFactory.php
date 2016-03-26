<?php

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\RestApi\ApiRouter;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsApiV1GetRequestHandler;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsLocator;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsService;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationTypeCode;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\SameSeriesProductRelations;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchAutosuggestionRequestHandler;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchRequestHandler;
use LizardsAndPumpkins\ProductListing\ContentDelivery\SelectProductListingRobotsMetaTagContent;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\UNUSED\PricesJsonSnippetTransformation;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation;
use LizardsAndPumpkins\ProductDetail\ContentDelivery\SimpleEuroPriceSnippetTransformation;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\Routing\GenericHttpRouter;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRouter;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductCanonicalTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler;
use LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

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
            __DIR__ . '/../../tests/shared-fixture' :
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
            $this->getMasterFactory()->getTranslatorRegistry(),
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
            $this->getMasterFactory()->createProductListingSnippetKeyGenerator(),
            $this->getMasterFactory()->createProductListingFacetFiltersToIncludeInResult(),
            $this->getMasterFactory()->createProductListingPageContentBuilder(),
            $this->getMasterFactory()->createSelectProductListingRobotsMetaTagContent(),
            $this->getMasterFactory()->createProductListingPageRequest()
        );
    }

    /**
     * @return SelectProductListingRobotsMetaTagContent
     */
    public function createSelectProductListingRobotsMetaTagContent()
    {
        return new SelectProductListingRobotsMetaTagContent();
    }

    /**
     * @return FacetFiltersToIncludeInResult
     */
    public function createProductListingFacetFiltersToIncludeInResult()
    {
        $facetFields = $this->getMasterFactory()->getProductListingFacetFilterRequestFields($this->createContext());
        return new FacetFiltersToIncludeInResult(...$facetFields);
    }

    /**
     * @return FacetFiltersToIncludeInResult
     */
    public function createProductSearchFacetFiltersToIncludeInResult()
    {
        $facetFields = $this->getMasterFactory()->getProductSearchFacetFilterRequestFields($this->createContext());
        return new FacetFiltersToIncludeInResult(...$facetFields);
    }

    /**
     * @return ProductListingPageContentBuilder
     */
    public function createProductListingPageContentBuilder()
    {
        return new ProductListingPageContentBuilder(
            $this->getMasterFactory()->createProductJsonService(),
            $this->getMasterFactory()->createPageBuilder(),
            $this->getMasterFactory()->createSearchFieldToRequestParamMap($this->createContext()),
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
            $this->getMasterFactory()->createSearchFieldToRequestParamMap($this->createContext()),
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
            ProductListingTemplateSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingTemplateSnippetKeyGenerator();
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
            ProductListingSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingSnippetRenderer::CANONICAL_TAG_KEY,
            function () {
                return $this->getMasterFactory()->createProductListingCanonicalTagSnippetKeyGenerator();
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
        $registrySnippetKeyGeneratorLocator->register(
            ProductDetailViewSnippetRenderer::TITLE_KEY_CODE,
            function () {
                return $this->getMasterFactory()->createProductTitleSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductCanonicalTagSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductCanonicalTagSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingTitleSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingTitleSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingDescriptionSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingDescriptionSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductDetailViewSnippetRenderer::HTML_HEAD_META_CODE,
            function () {
                return $this->getMasterFactory()->createProductDetailPageMetaDescriptionSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingSnippetRenderer::HTML_HEAD_META_KEY,
            function () {
                return $this->getMasterFactory()->createHtmlHeadMetaKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductDetailPageRobotsMetaTagSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductDetailPageRobotsMetaTagSnippetKeyGenerator();
            }
        );
        $registrySnippetKeyGeneratorLocator->register(
            ProductListingRobotsMetaTagSnippetRenderer::CODE,
            function () {
                return $this->getMasterFactory()->createProductListingPageRobotsMetaTagSnippetKeyGenerator();
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
            $this->getMasterFactory()->getSnippetKeyGeneratorLocator()
        );
        $this->registerSnippetTransformations($pageBuilder);

        return $pageBuilder;
    }

    private function registerSnippetTransformations(PageBuilder $pageBuilder)
    {
        $pageBuilder->registerSnippetTransformation(
            ProductJsonSnippetRenderer::CODE,
            $this->getMasterFactory()->createProductJsonSnippetTransformation()
        );

        // Todo: remove when product detail page uses product json only
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
            $this->getMasterFactory()->createProductSearchFacetFiltersToIncludeInResult(),
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
