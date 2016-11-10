<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\GenericHttpRouter;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ContentDelivery\SimpleEuroPriceSnippetTransformation;
use LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductCanonicalTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\ProductListing\ContentDelivery\SelectProductListingRobotsMetaTagContent;
use LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer;
use LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory
 * @covers \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
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
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPricesBuilder
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonServiceBuilder
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Http\Routing\GenericHttpRouter
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
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
        $masterFactory->register(new UnitTestFactory($this));

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $this->frontendFactory = new FrontendFactory($request);
        $masterFactory->register($this->frontendFactory);
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
     * @dataProvider registeredSnippetCodeDataProvider
     */
    public function testSnippetKeyGeneratorForGivenCodeIsReturned(string $snippetCode)
    {
        $snippetKeyGeneratorLocator = $this->frontendFactory->createRegistrySnippetKeyGeneratorLocatorStrategy();
        $result = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    /**
     * @return array[]
     */
    public function registeredSnippetCodeDataProvider() : array
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
