<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\Routing\HttpRouter;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\DefaultWebFront
 * @covers \LizardsAndPumpkins\Http\WebFront
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchAutosuggestionRequestHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationTypeCode
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsLocator
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsService
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Http\Routing\GenericHttpRouter
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\QueueImportCommands
 * @uses   \LizardsAndPumpkins\Import\Product\ProductImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Http\Routing\ResourceNotFoundRouter
 * @uses   \LizardsAndPumpkins\Http\Routing\ResourceNotFoundRequestHandler
 * @uses   \LizardsAndPumpkins\Http\Routing\HttpRouterChain
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\Website\ContextWebsite
 * @uses   \LizardsAndPumpkins\Context\Locale\ContextLocale
 * @uses   \LizardsAndPumpkins\Context\Country\ContextCountry
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\RestApi\ApiRouter
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\FileSystem\Directory
 */
class DefaultWebFrontTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultWebFront
     */
    private $webFront;

    /**
     * @var HttpResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockHttpResponse;

    protected function setUp()
    {
        $routerFactoryMethods = [
            'createApiRouter',
            'createProductDetailViewRouter',
            'createProductListingRouter',
            'createResourceNotFoundRouter',
            'createProductSearchResultRouter',
            'createProductSearchAutosuggestionRouter',
        ];

        $stubFactoryMethods = array_merge(
            ['getContext', 'createHttpRouterChain', 'register'],
            $routerFactoryMethods
        );

        /** @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject $stubMasterFactory */
        $stubMasterFactory = $this->getMockBuilder(MasterFactory::class)->setMethods($stubFactoryMethods)->getMock();

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $mockRouterChain = $this->getMock(HttpRouterChain::class);
        $mockHttpRequestHandler = $this->getMock(HttpRequestHandler::class);
        $this->mockHttpResponse = $this->getMock(HttpResponse::class);

        array_map(function ($methodName) use ($stubMasterFactory) {
            $stubMasterFactory->method($methodName)->willReturn($this->getMock(HttpRouter::class));
        }, $routerFactoryMethods);

        $stubMasterFactory->method('getContext')->willReturn($this->getMock(Context::class));

        $stubMasterFactory->method('createHttpRouterChain')->willReturn($mockRouterChain);
        $mockRouterChain->method('route')->willReturn($mockHttpRequestHandler);
        $mockHttpRequestHandler->method('process')->willReturn($this->mockHttpResponse);

        $this->webFront = new TestDefaultWebFront($stubHttpRequest, $stubMasterFactory);
    }

    public function testMasterFactoryIsReturned()
    {
        $result = $this->webFront->getMasterFactory();
        $this->assertInstanceOf(MasterFactory::class, $result);
    }

    public function testSendMethodOfResponseIsCalled()
    {
        $this->mockHttpResponse->expects($this->once())->method('send');
        $this->webFront->run();
    }

    public function testSampleMasterFactoryIsReturned()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getPathWithoutWebsitePrefix')->willReturn('foo');

        $webFront = new DefaultWebFront($stubHttpRequest, new UnitTestFactory());
        $webFront->registerFactory(new UnitTestFactory());
        $webFront->processRequest();

        $this->assertInstanceOf(SampleMasterFactory::class, $webFront->getMasterFactory());
    }
}
