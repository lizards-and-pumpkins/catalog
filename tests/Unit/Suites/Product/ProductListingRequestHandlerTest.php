<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;
use Brera\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
use Brera\Http\UnableToHandleRequestException;
use Brera\PageBuilder;
use Brera\Pagination;
use Brera\Renderer\BlockRenderer;
use Brera\SnippetKeyGenerator;
use Brera\SnippetKeyGeneratorLocator;

/**
 * @covers \Brera\Product\ProductListingRequestHandler
 * @uses   \Brera\Pagination
 * @uses   \Brera\Product\ProductId
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetContent
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion
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
    private $testMetaInfoKey = 'stub-meta-info-key';

    /**
     * @var string
     */
    private $testDefaultNumberOfProductsPerPageSnippetKey = 'test-default-number-of-products-per-page-snippet-key';

    /**
     * @var int
     */
    private $testDefaultNumberOfProductsPerPage = 1;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var FilterNavigationFilterCollection|\PHPUnit_Framework_MockObject_MockObject $stubFilterCollection
     */
    private $stubFilterCollection;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductInListingSnippetKeyGenerator;

    private function mockDataPoolReader()
    {
        /** @var CompositeSearchCriterion|\PHPUnit_Framework_MockObject_MockObject $stubSelectionCriteria */
        $stubSelectionCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $stubSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []]);

        $pageSnippetCodes = ['child-snippet1'];

        $testMetaInfoSnippetJson = json_encode(ProductListingMetaInfoSnippetContent::create(
            $stubSelectionCriteria,
            'root-snippet-code',
            $pageSnippetCodes
        )->getInfo());

        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $testMetaInfoSnippetJson],
            [$this->testDefaultNumberOfProductsPerPageSnippetKey, $this->testDefaultNumberOfProductsPerPage]
        ]);
    }

    /**
     * @return SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
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
        $this->stubProductInListingSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductInListingSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn('stub-product-snippet-key');

        $stubProductListingMetaInfoSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class, [], [], '', false);
        $stubProductListingMetaInfoSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testMetaInfoKey);

        $stubDefaultProductsPerPageSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class, [], [], '', false);
        $stubDefaultProductsPerPageSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($this->testDefaultNumberOfProductsPerPageSnippetKey);

        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturnMap([
            [ProductListingMetaInfoSnippetRenderer::CODE, $stubProductListingMetaInfoSnippetKeyGenerator],
            [ProductInListingSnippetRenderer::CODE, $this->stubProductInListingSnippetKeyGenerator],
            [DefaultNumberOfProductsPerPageSnippetRenderer::CODE, $stubDefaultProductsPerPageSnippetKeyGenerator]
        ]);

        return $stubSnippetKeyGeneratorLocator;
    }

    /**
     * @param ProductId[] $productIds
     * @return SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentCollectionContainingDocumentsWithGivenProductIds(array $productIds)
    {
        $stubSearchDocuments = array_map(function (ProductId $productId) {
            $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
            $stubSearchDocument->method('getProductId')->willReturn($productId);
            return $stubSearchDocument;
        }, $productIds);

        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getDocuments')->willReturn($stubSearchDocuments);
        $stubSearchDocumentCollection->method('count')->willReturn(count($stubSearchDocuments));

        return $stubSearchDocumentCollection;
    }

    protected function setUp()
    {
        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubSnippetKeyGeneratorLocator = $this->createStubSnippetKeyGeneratorLocator();

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubFilterNavigationBlockRenderer */
        $stubFilterNavigationBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);

        $this->stubFilterCollection = $this->getMock(FilterNavigationFilterCollection::class, [], [], '', false);

        $stubFilterNavigationAttributeCodes = ['foo'];

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubPaginationBlockRenderer */
        $stubPaginationBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);

        $this->requestHandler = new ProductListingRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $stubSnippetKeyGeneratorLocator,
            $stubFilterNavigationBlockRenderer,
            $this->stubFilterCollection,
            $stubFilterNavigationAttributeCodes,
            $stubPaginationBlockRenderer
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
        $this->mockDataPoolReader();
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testPageMetaInfoIsOnlyLoadedOnce()
    {
        $this->mockDataPoolReader();

        $this->mockDataPoolReader->expects($this->once())->method('getSnippet')->with($this->testMetaInfoKey);

        $this->requestHandler->canProcess($this->stubRequest);
        $this->requestHandler->canProcess($this->stubRequest);
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

        $this->mockDataPoolReader();
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

        $this->mockDataPoolReader();
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

        $this->mockDataPoolReader();
        $this->requestHandler->process($this->stubRequest);
    }

    public function testProductsInListingAreAddedToPageBuilder()
    {
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubSearchDocumentCollection);

        $this->mockPageBuilder->expects($this->atLeastOnce())->method('addSnippetsToPage');

        $this->mockDataPoolReader();
        $this->requestHandler->process($this->stubRequest);
    }

    public function testOnlyProductsFromACurrentPageAreAddedToPageBuilder()
    {
        $currentPageNumber = 2;

        $this->stubRequest->method('getQueryParameter')->willReturnMap(
            [[Pagination::PAGINATION_QUERY_PARAMETER_NAME, $currentPageNumber]]
        );

        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $productIds = [$productAId, $productBId];

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollectionContainingDocumentsWithGivenProductIds(
            $productIds
        );

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubSearchDocumentCollection);

        $spy = $this->any();
        $this->stubProductInListingSnippetKeyGenerator->expects($spy)->method('getKeyForContext');

        $this->mockDataPoolReader();

        $this->requestHandler->process($this->stubRequest);

        $expectedProductIds = array_slice(
            $productIds,
            ($currentPageNumber - 1) * $this->testDefaultNumberOfProductsPerPage,
            $this->testDefaultNumberOfProductsPerPage
        );
        $productIdsAddedToBuilder = [];

        /** @var \PHPUnit_Framework_MockObject_Invocation_Object $invocation */
        foreach ($spy->getInvocations() as $invocation) {
            if (!is_array($invocation->parameters[1]) || !isset($invocation->parameters[1]['product_id'])) {
                continue;
            }
            $productIdsAddedToBuilder[] = $invocation->parameters[1]['product_id'];
        }

        $this->assertEquals($expectedProductIds, $productIdsAddedToBuilder);
    }

    public function testSelectedFiltersAreNotAppliedToEmptyCollection()
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('count')->willReturn(0);
        $stubSearchDocumentCollection->expects($this->never())->method('getCollectionFilteredByCriteria');

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubSearchDocumentCollection);

        $this->mockDataPoolReader();
        $this->requestHandler->process($this->stubRequest);
    }

    public function testFiltersAreAppliedToSelectionCriteriaIfSelected()
    {
        $this->mockDataPoolReader();

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();
        $this->stubRequest->method('getQueryParameter')->willReturnMap([['foo', 'bar']]);

        $filterCriterion = SearchCriterionEqual::create('foo', 'bar');
        $filterCriteria = CompositeSearchCriterion::createOr($filterCriterion);
        $originalCriteria = CompositeSearchCriterion::createAnd();
        $expectedCriteria = CompositeSearchCriterion::createAnd($filterCriteria, $originalCriteria);

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->expects($this->once())->method('getSearchDocumentsMatchingCriteria')
            ->with($expectedCriteria)
            ->willReturn($stubSearchDocumentCollection);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPaginationSnippetIsAddedToPageBuilder()
    {
        $this->mockDataPoolReader();

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();

        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);
        $this->mockDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubSearchDocumentCollection);

        $spy = $this->any();

        $this->mockPageBuilder->expects($spy)->method('addSnippetsToPage');

        $this->requestHandler->process($this->stubRequest);

        $numberOfTimesPaginationSnippetHasBeenAddedToPageBuilder = 0;

        /** @var \PHPUnit_Framework_MockObject_Invocation_Object $invocation */
        foreach ($spy->getInvocations() as $invocation) {
            if (['pagination' => 'pagination'] === $invocation->parameters[0]) {
                $numberOfTimesPaginationSnippetHasBeenAddedToPageBuilder ++;
            }
        }

        $this->assertEquals(
            1,
            $numberOfTimesPaginationSnippetHasBeenAddedToPageBuilder,
            'Failed to assert pagination snippet was added to page builder.'
        );
    }
}
