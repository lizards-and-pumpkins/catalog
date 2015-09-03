<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
use Brera\Http\UnableToHandleRequestException;
use Brera\PageBuilder;
use Brera\Renderer\BlockRenderer;
use Brera\SnippetKeyGenerator;
use Brera\SnippetKeyGeneratorLocator;

/**
 * @covers \Brera\Product\ProductListingRequestHandler
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetContent
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria
 * @uses   \Brera\DataPool\SearchEngine\SearchCriterion
 */
class ProductListingRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var ProductListingRequestHandler
     */
    private $requestHandler;

    /**
     * @var string
     */
    private $testMetaInfoKey;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var FilterNavigationFilterCollection|\PHPUnit_Framework_MockObject_MockObject $stubFilterCollection
     */
    private $stubFilterCollection;

    private function mockMetaInfoSnippet()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $mockSelectionCriteria */
        $mockSelectionCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
        $mockSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => SearchCriteria::AND_CONDITION, 'criteria' => []]);

        $pageSnippetCodes = ['child-snippet1'];

        $testMetaInfoSnippetJson = json_encode(ProductListingMetaInfoSnippetContent::create(
            $mockSelectionCriteria,
            'root-snippet-code',
            $pageSnippetCodes
        )->getInfo());

        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $testMetaInfoSnippetJson]
        ]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentCollection()
    {
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);
        $stubSearchDocumentCollection->method('count')->willReturn(1);

        return $stubSearchDocumentCollection;
    }

    /**
     * @return SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSnippetKeyGeneratorLocator()
    {
        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testMetaInfoKey);

        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturn($mockSnippetKeyGenerator);

        return $stubSnippetKeyGeneratorLocator;
    }

    protected function setUp()
    {
        $this->testMetaInfoKey = 'stub-meta-info-key';

        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubSnippetKeyGeneratorLocator = $this->createStubSnippetKeyGeneratorLocator();

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubFilterNavigationBlockRenderer */
        $stubFilterNavigationBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);

        $this->stubFilterCollection = $this->getMock(FilterNavigationFilterCollection::class, [], [], '', false);

        $stubFilterNavigationAttributeCodes = ['foo'];

        $this->requestHandler = new ProductListingRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $stubSnippetKeyGeneratorLocator,
            $stubFilterNavigationBlockRenderer,
            $this->stubFilterCollection,
            $stubFilterNavigationAttributeCodes
        );

        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testFalseIsReturnedIfThePageMetaInfoContentSnippetCanNotBeLoaded()
    {
        $exception = new KeyNotFoundException();
        $this->mockDataPoolReader->method('getSnippet')->willThrowException($exception);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testTrueIsReturnedIfThePageMetaInfoContentSnippetCanBeLoaded()
    {
        $this->mockMetaInfoSnippet();
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testExceptionIsThrownIfProcessWithoutMetaInfoContentIsCalled()
    {
        $this->setExpectedException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubRequest);
    }

    public function testPageMetaInfoSnippetIsCreated()
    {
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubSearchDocumentCollection);

        $this->mockMetaInfoSnippet();
        $this->requestHandler->process($this->stubRequest);

        $this->assertAttributeInstanceOf(
            ProductListingMetaInfoSnippetContent::class,
            'pageMetaInfo',
            $this->requestHandler
        );
    }

    public function testPageIsReturned()
    {
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubSearchDocumentCollection);

        $this->mockMetaInfoSnippet();
        $this->mockPageBuilder->method('buildPage')->willReturn($this->getMock(HttpResponse::class, [], [], '', false));

        $this->assertInstanceOf(HttpResponse::class, $this->requestHandler->process($this->stubRequest));
    }

    public function testNoSnippetsAreAddedToPageBuilderIfListingIsEmpty()
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('count')->willReturn(0);

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubSearchDocumentCollection);

        $this->mockPageBuilder->expects($this->never())->method('addSnippetsToPage');

        $this->mockMetaInfoSnippet();
        $this->requestHandler->process($this->stubRequest);
    }

    public function testProductsInListingAreAddedToPageBuilder()
    {
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubSearchDocumentCollection);

        $this->mockPageBuilder->expects($this->atLeastOnce())->method('addSnippetsToPage');

        $this->mockMetaInfoSnippet();
        $this->requestHandler->process($this->stubRequest);
    }

    public function testSelectedFiltersAreNotAppliedToEmptyCollection()
    {
        $mockSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $mockSearchDocumentCollection->method('count')->willReturn(0);
        $mockSearchDocumentCollection->expects($this->never())->method('getCollectionFilteredByCriteria');

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($mockSearchDocumentCollection);

        $this->mockMetaInfoSnippet();
        $this->requestHandler->process($this->stubRequest);
    }

    public function testNoFiltersAreAppliedToSelectionCriteriaIfNoAttributesAreSetToBeDisplayedInFilterNavigation()
    {
        $this->mockMetaInfoSnippet();
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();

        $originalCriteria = SearchCriteria::createAnd();

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->expects($this->once())->method('getSearchDocumentsMatchingCriteria')
            ->with($originalCriteria)
            ->willReturn($stubSearchDocumentCollection);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubSnippetKeyGeneratorLocator = $this->createStubSnippetKeyGeneratorLocator();

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubFilterNavigationBlockRenderer */
        $stubFilterNavigationBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);

        $stubFilterNavigationAttributeCodes = [];

        $this->requestHandler = new ProductListingRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $stubSnippetKeyGeneratorLocator,
            $stubFilterNavigationBlockRenderer,
            $this->stubFilterCollection,
            $stubFilterNavigationAttributeCodes
        );

        $this->requestHandler->process($this->stubRequest);
    }

    public function testFiltersAreAppliedToSelectionCriteriaIfSelected()
    {
        $this->mockMetaInfoSnippet();

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();
        $this->stubRequest->method('getQueryParameter')->with('foo')->willReturn('bar');

        $filterCriteria = SearchCriteria::createOr();
        $filterCriteria->addCriterion(SearchCriterion::create('foo', 'bar', '='));
        $originalCriteria = SearchCriteria::createAnd();
        $expectedCriteria = SearchCriteria::createAnd();
        $expectedCriteria->addCriteria($filterCriteria);
        $expectedCriteria->addCriteria($originalCriteria);

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->expects($this->once())->method('getSearchDocumentsMatchingCriteria')
            ->with($expectedCriteria)
            ->willReturn($stubSearchDocumentCollection);

        $this->requestHandler->process($this->stubRequest);
    }
}
