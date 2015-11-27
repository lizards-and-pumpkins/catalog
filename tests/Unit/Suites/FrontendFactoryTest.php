<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Api\ApiRouter;
use LizardsAndPumpkins\Content\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\ContentDelivery\Catalog\ProductsPerPage;
use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\PricesJsonSnippetTransformation;
use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\SimpleEuroPriceSnippetTransformation;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\GenericHttpRouter;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpsUrl;
use LizardsAndPumpkins\Product\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Product\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\Product\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

/**
 * @covers \LizardsAndPumpkins\FrontendFactory
 * @covers \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\SampleMasterFactory
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 * @uses   \LizardsAndPumpkins\CommonFactory
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingPageRequest
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchAutosuggestionRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductsPerPage
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection
 * @uses   \LizardsAndPumpkins\ContentDelivery\SnippetTransformation\PricesJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextLocale
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextCountry
 * @uses   \LizardsAndPumpkins\Content\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Http\GenericHttpRouter
 * @uses   \LizardsAndPumpkins\Product\MultipleProductStockQuantityApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequest
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\InMemorySearchEngine
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\RegistrySnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\Api\ApiRouter
 * @uses   \LizardsAndPumpkins\Api\ApiRequestHandlerChain
 * @uses   \LizardsAndPumpkins\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\PageBuilder
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 * @uses   \LizardsAndPumpkins\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Utils\Directory
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToProductBuilder
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

    public function testSameProductsPerPageIsReturnedViaGetter()
    {
        $result1 = $this->frontendFactory->getProductsPerPageConfig();
        $result2 = $this->frontendFactory->getProductsPerPageConfig();

        $this->assertInstanceOf(ProductsPerPage::class, $result1);
        $this->assertSame($result1, $result2);
    }

    /**
     * @dataProvider registeredSnippetCodeDataProvider
     * @param string $snippetCode
     */
    public function testContentBlockSnippetKeyGeneratorLocatorReturnsSnippetKeyGenerator($snippetCode)
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
            [ProductListingPageSnippetRenderer::CODE],
            ['price'],
            ['backorders'],
            [ProductListingCriteriaSnippetRenderer::CODE],
            [ProductSearchResultMetaSnippetRenderer::CODE],
            [ProductSearchAutosuggestionMetaSnippetRenderer::CODE],
            [ProductSearchAutosuggestionSnippetRenderer::CODE],
            [ProductJsonSnippetRenderer::CODE],
            [ConfigurableProductJsonSnippetRenderer::VARIATION_ATTRIBUTES_CODE],
            [ConfigurableProductJsonSnippetRenderer::ASSOCIATED_PRODUCTS_CODE],
        ];
    }
}
