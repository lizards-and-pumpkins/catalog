<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpRouter;
use LizardsAndPumpkins\Http\HttpRouterChain;

/**
 * @covers \LizardsAndPumpkins\DefaultWebFront
 * @covers \LizardsAndPumpkins\WebFront
 * @uses   \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\FrontendFactory
 * @uses   \LizardsAndPumpkins\CommonFactory
 * @uses   \LizardsAndPumpkins\Content\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingPageRequest
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchAutosuggestionRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductsPerPage
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductJsonService
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection
 * @uses   \LizardsAndPumpkins\ContentDelivery\SnippetTransformation\PricesJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationTypeCode
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationsLocator
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationsService
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationsApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\InMemorySearchEngine
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\ContentDelivery\PageBuilder
 * @uses   \LizardsAndPumpkins\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Http\GenericHttpRouter
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator\RegistrySnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Http\ResourceNotFoundRouter
 * @uses   \LizardsAndPumpkins\Http\ResourceNotFoundRequestHandler
 * @uses   \LizardsAndPumpkins\Http\HttpRouterChain
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextLocale
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder\ContextCountry
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Api\ApiRouter
 * @uses   \LizardsAndPumpkins\Api\ApiRequestHandlerLocator
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 * @uses   \LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Utils\Directory
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
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn('foo');

        $webFront = new DefaultWebFront($stubHttpRequest);
        $webFront->registerFactory(new UnitTestFactory());
        $webFront->runWithoutSendingResponse();

        $this->assertInstanceOf(SampleMasterFactory::class, $webFront->getMasterFactory());
    }
}
