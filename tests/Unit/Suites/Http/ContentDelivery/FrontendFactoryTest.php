<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\MetaSnippetBasedRouter;
use LizardsAndPumpkins\Http\Routing\UnknownHttpRequestMethodHandler;
use LizardsAndPumpkins\Http\Routing\UnknownHttpRequestMethodRouter;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ContentDelivery\SimpleEuroPriceSnippetTransformation;
use LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailMetaSnippetRenderer;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchSharedFactory;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory
 * @covers \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryWithCallbackTrait
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\CatalogMasterFactory
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Http\Routing\MetaSnippetBasedRouter
 * @uses   \LizardsAndPumpkins\Http\Routing\UnknownHttpRequestMethodRouter
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SnippetReader
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\GenericPageBuilder
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
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiFactory
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchSharedFactory
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator
 * @uses   \LizardsAndPumpkins\RestApi\RestApiFactory
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\DefaultFullTextCriteriaBuilder
 */
class FrontendFactoryTest extends TestCase
{
    /**
     * @var FrontendFactory
     */
    private $factory;

    public function setUp()
    {
        $masterFactory = new CatalogMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new UnitTestFactory($this));
        $masterFactory->register(new RestApiFactory());
        $masterFactory->register(new ProductSearchSharedFactory());

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $this->factory = new FrontendFactory($request);
        $masterFactory->register($this->factory);
    }

    public function testReturnsMetaSnippetBasedRouter()
    {
        $result = $this->factory->createMetaSnippetBasedRouter();
        $this->assertInstanceOf(MetaSnippetBasedRouter::class, $result);
    }

    public function testReturnsUnknownHttpRequestMethodRouter()
    {
        $result = $this->factory->createUnknownHttpRequestMethodRouter();
        $this->assertInstanceOf(UnknownHttpRequestMethodRouter::class, $result);
    }

    public function testReturnsUnknownHttpRequestMethodHandler()
    {
        $result = $this->factory->createUnknownHttpRequestMethodHandler();
        $this->assertInstanceOf(UnknownHttpRequestMethodHandler::class, $result);
    }

    public function testProductListingFilterNavigationConfigIsInstanceOfFacetFilterRequest()
    {
        $result = $this->factory->createProductListingFacetFiltersToIncludeInResult();
        $this->assertInstanceOf(FacetFiltersToIncludeInResult::class, $result);
    }

    public function testProductSearchResultsFilterNavigationConfigIsInstanceOfFacetFilterRequest()
    {
        $result = $this->factory->createProductSearchFacetFiltersToIncludeInResult();
        $this->assertInstanceOf(FacetFiltersToIncludeInResult::class, $result);
    }

    public function testSameKeyGeneratorLocatorIsReturnedViaGetter()
    {
        $result1 = $this->factory->getSnippetKeyGeneratorLocator();
        $result2 = $this->factory->getSnippetKeyGeneratorLocator();
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $result1);
        $this->assertSame($result1, $result2);
    }

    public function testItReturnsAContext()
    {
        $this->assertInstanceOf(Context::class, $this->factory->createContext());
    }

    public function testItReturnsASimpleEuroPriceSnippetTransformation()
    {
        $result = $this->factory->createPriceSnippetTransformation();
        $this->assertInstanceOf(SimpleEuroPriceSnippetTransformation::class, $result);
    }

    public function testItReturnsAProductPricesJsonSnippetTransformation()
    {
        $result = $this->factory->createPricesJsonSnippetTransformation();
        $this->assertInstanceOf(PricesJsonSnippetTransformation::class, $result);
    }

    /**
     * @dataProvider registeredSnippetCodeDataProvider
     */
    public function testSnippetKeyGeneratorForGivenCodeIsReturned(string $snippetCode)
    {
        $snippetKeyGeneratorLocator = $this->factory->createRegistrySnippetKeyGeneratorLocatorStrategy();
        $result = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    /**
     * @return array[]
     */
    public function registeredSnippetCodeDataProvider(): array
    {
        return [
            [ProductDetailMetaSnippetRenderer::CODE],
            [ProductInListingSnippetRenderer::CODE],
            [ProductListingTemplateSnippetRenderer::CODE],
            [PriceSnippetRenderer::PRICE],
            [PriceSnippetRenderer::SPECIAL_PRICE],
            [ProductListingSnippetRenderer::CODE],
            [ProductSearchResultMetaSnippetRenderer::CODE],
            [ProductJsonSnippetRenderer::CODE],
            [ConfigurableProductJsonSnippetRenderer::VARIATION_ATTRIBUTES_CODE],
            [ConfigurableProductJsonSnippetRenderer::ASSOCIATED_PRODUCTS_CODE],
        ];
    }

    public function testItReturnsAProductJsonSnippetTransformation()
    {
        $result = $this->factory->createProductJsonSnippetTransformation();
        $this->assertInstanceOf(ProductJsonSnippetTransformation::class, $result);
    }

    public function testReturnsProductListingPageContentBuilder()
    {
        $this->assertInstanceOf(
            ProductListingPageContentBuilder::class,
            $this->factory->createProductListingPageContentBuilder()
        );
    }

    public function testReturnsProductListingPageRequest()
    {
        $this->assertInstanceOf(ProductListingPageRequest::class, $this->factory->createProductListingPageRequest());
    }

    public function testReturnsPageBuilder()
    {
        $this->assertInstanceOf(PageBuilder::class, $this->factory->createPageBuilder());
    }
}
