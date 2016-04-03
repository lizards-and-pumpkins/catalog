<?php

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\RestApi\ApiRouter;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsApiV1GetRequestHandler;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsLocator;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsService;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\SameSeriesProductRelations;
use LizardsAndPumpkins\ProductListing\ContentDelivery\SelectProductListingRobotsMetaTagContent;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation;
use LizardsAndPumpkins\ProductDetail\ContentDelivery\SimpleEuroPriceSnippetTransformation;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\Routing\GenericHttpRouter;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpsUrl;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductCanonicalTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory
 * @covers \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\SampleMasterFactory
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchAutosuggestionRequestHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsService
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsLocator
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationTypeCode
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\SameSeriesProductRelations
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\Website\ContextWebsite
 * @uses   \LizardsAndPumpkins\Context\Locale\ContextLocale
 * @uses   \LizardsAndPumpkins\Context\Country\ContextCountry
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Http\Routing\GenericHttpRouter
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\InMemory\InMemorySearchEngine
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\RestApi\ApiRouter
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Util\FileSystem\Directory
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 * @uses   \LizardsAndPumpkins\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\QueueImportCommands
 * @uses   \LizardsAndPumpkins\Import\Product\ProductImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator
 */
class FrontendFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FrontendFactory
     */
    private $frontendFactory;

    public function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new UnitTestFactory());

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpsUrl::fromString('http://example.com/'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->frontendFactory = new FrontendFactory($request);
        $masterFactory->register($this->frontendFactory);
    }

    public function testCatalogImportApiRequestHandlerIsReturned()
    {
        $result = $this->frontendFactory->createCatalogImportApiV1PutRequestHandler();
        $this->assertInstanceOf(CatalogImportApiV1PutRequestHandler::class, $result);
    }

    public function testContentBlocksApiRequestHandlerIsReturned()
    {
        $result = $this->frontendFactory->createContentBlocksApiV1PutRequestHandler();
        $this->assertInstanceOf(ContentBlocksApiV1PutRequestHandler::class, $result);
    }

    public function testApiRouterIsReturned()
    {
        $result = $this->frontendFactory->createApiRouter();
        $this->assertInstanceOf(ApiRouter::class, $result);
    }

    public function testProductDetailViewRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductDetailViewRouter();
        $this->assertInstanceOf(GenericHttpRouter::class, $result);
    }

    public function testProductListingRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductListingRouter();
        $this->assertInstanceOf(GenericHttpRouter::class, $result);
    }

    public function testProductListingFilterNavigationConfigIsInstanceOfFacetFilterRequest()
    {
        $result = $this->frontendFactory->createProductListingFacetFiltersToIncludeInResult();
        $this->assertInstanceOf(FacetFiltersToIncludeInResult::class, $result);
    }

    public function testProductSearchResultsFilterNavigationConfigIsInstanceOfFacetFilterRequest()
    {
        $result = $this->frontendFactory->createProductSearchFacetFiltersToIncludeInResult();
        $this->assertInstanceOf(FacetFiltersToIncludeInResult::class, $result);
    }

    public function testSameKeyGeneratorLocatorIsReturnedViaGetter()
    {
        $result1 = $this->frontendFactory->getSnippetKeyGeneratorLocator();
        $result2 = $this->frontendFactory->getSnippetKeyGeneratorLocator();
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $result1);
        $this->assertSame($result1, $result2);
    }

    public function testItReturnsAContext()
    {
        $this->assertInstanceOf(Context::class, $this->frontendFactory->createContext());
    }

    public function testProductSearchResultRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductSearchResultRouter();
        $this->assertInstanceOf(GenericHttpRouter::class, $result);
    }

    public function testItReturnsASimpleEuroPriceSnippetTransformation()
    {
        $result = $this->frontendFactory->createPriceSnippetTransformation();
        $this->assertInstanceOf(SimpleEuroPriceSnippetTransformation::class, $result);
    }

    public function testItReturnsAProductPricesJsonSnippetTransformation()
    {
        $result = $this->frontendFactory->createPricesJsonSnippetTransformation();
        $this->assertInstanceOf(PricesJsonSnippetTransformation::class, $result);
    }

    public function testProductSearchAutosuggestionRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductSearchAutosuggestionRouter();
        $this->assertInstanceOf(GenericHttpRouter::class, $result);
    }

    /**
     * @param string $snippetCode
     * @dataProvider registeredSnippetCodeDataProvider
     */
    public function testSnippetKeyGeneratorForGivenCodeIsReturned($snippetCode)
    {
        $snippetKeyGeneratorLocator = $this->frontendFactory->createRegistrySnippetKeyGeneratorLocatorStrategy();
        $result = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    /**
     * @return array[]
     */
    public function registeredSnippetCodeDataProvider()
    {
        return [
            [ProductDetailViewSnippetRenderer::CODE],
            [ProductInSearchAutosuggestionSnippetRenderer::CODE],
            [ProductInListingSnippetRenderer::CODE],
            [ProductListingTemplateSnippetRenderer::CODE],
            [PriceSnippetRenderer::PRICE],
            [PriceSnippetRenderer::SPECIAL_PRICE],
            [ProductListingSnippetRenderer::CODE],
            [ProductSearchResultMetaSnippetRenderer::CODE],
            [ProductSearchAutosuggestionMetaSnippetRenderer::CODE],
            [ProductSearchAutosuggestionSnippetRenderer::CODE],
            [ProductJsonSnippetRenderer::CODE],
            [ConfigurableProductJsonSnippetRenderer::VARIATION_ATTRIBUTES_CODE],
            [ConfigurableProductJsonSnippetRenderer::ASSOCIATED_PRODUCTS_CODE],
            [ProductListingSnippetRenderer::CANONICAL_TAG_KEY],
            [ProductDetailViewSnippetRenderer::TITLE_KEY_CODE],
            [ProductListingTitleSnippetRenderer::CODE],
            [ProductListingDescriptionSnippetRenderer::CODE],
            [ProductDetailViewSnippetRenderer::HTML_HEAD_META_CODE],
            [ProductCanonicalTagSnippetRenderer::CODE],
            [ProductListingSnippetRenderer::HTML_HEAD_META_KEY],
            [ProductListingRobotsMetaTagSnippetRenderer::CODE],
            [ProductDetailPageRobotsMetaTagSnippetRenderer::CODE],
        ];
    }

    public function testItReturnsAProductRelationsService()
    {
        $result = $this->frontendFactory->createProductRelationsService();
        $this->assertInstanceOf(ProductRelationsService::class, $result);
    }

    public function testItReturnsAProductRelationsLocator()
    {
        $result = $this->frontendFactory->createProductRelationsLocator();
        $this->assertInstanceOf(ProductRelationsLocator::class, $result);
    }

    public function testItCreatesProductRelationsApiV1GetRequestHandler()
    {
        $result = $this->frontendFactory->createProductRelationsApiV1GetRequestHandler();
        $this->assertInstanceOf(ProductRelationsApiV1GetRequestHandler::class, $result);
    }

    public function testItReturnsSameSeriesProductRelations()
    {
        $result = $this->frontendFactory->createSameSeriesProductRelations();
        $this->assertInstanceOf(SameSeriesProductRelations::class, $result);
    }

    public function testItReturnsAProductJsonService()
    {
        $result = $this->frontendFactory->createProductJsonService();
        $this->assertInstanceOf(ProductJsonService::class, $result);
    }

    public function testItReturnsAnEnrichProductJsonWithPrices()
    {
        $result = $this->frontendFactory->createEnrichProductJsonWithPrices();
        $this->assertInstanceOf(EnrichProductJsonWithPrices::class, $result);
    }

    public function testItReturnsAProductJsonSnippetTransformation()
    {
        $result = $this->frontendFactory->createProductJsonSnippetTransformation();
        $this->assertInstanceOf(ProductJsonSnippetTransformation::class, $result);
    }

    public function testReturnsSelectProductListingRobotsMetaTagContent()
    {
        $result = $this->frontendFactory->createSelectProductListingRobotsMetaTagContent();
        $this->assertInstanceOf(SelectProductListingRobotsMetaTagContent::class, $result);
    }
}
