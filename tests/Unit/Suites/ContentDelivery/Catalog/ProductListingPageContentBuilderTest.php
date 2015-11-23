<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 */
class ProductListingPageContentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSortOrderConfig;

    /**
     * @var ProductListingPageContentBuilder
     */
    private $pageContentBuilder;

    /**
     * @var PageMetaInfoSnippetContent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubPageMetaInfoSnippetContent;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var string[]
     */
    private $stubKeyGeneratorParams = [];

    /**
     * @var SearchEngineResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchEngineResponse;

    /**
     * @var ProductsPerPage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductsPerPage;

    /**
     * @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSelectedSortOrderConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $addSnippetsToPageSpy;

    private function createMockPageBuilder()
    {
        $mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        $this->addSnippetsToPageSpy = $this->any();
        $mockPageBuilder->expects($this->addSnippetsToPageSpy)->method('addSnippetsToPage');

        return $mockPageBuilder;
    }

    /**
     * @param string $snippetCode
     */
    private function assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode)
    {
        $numberOfTimesSnippetWasAddedToPageBuilder = array_sum(array_map(function ($invocation) use ($snippetCode) {
            return intval([$snippetCode => $snippetCode] === $invocation->parameters[0]);
        }, $this->addSnippetsToPageSpy->getInvocations()));

        $this->assertEquals(
            1,
            $numberOfTimesSnippetWasAddedToPageBuilder,
            sprintf('Failed to assert "%s" snippet was added to page builder.', $snippetCode)
        );
    }

    /**
     * @param string $snippetCode
     * @param string $snippetValue
     */
    private function assertDynamicSnippetWasAddedToPageBuilder($snippetCode, $snippetValue)
    {
        $numberOfTimesSnippetWasAddedToPageBuilder = array_sum(
            array_map(function ($invocation) use ($snippetCode, $snippetValue) {
                return intval([$snippetCode => $snippetCode] === $invocation->parameters[0] &&
                              [$snippetCode => $snippetValue] === $invocation->parameters[1]);
            }, $this->addSnippetsToPageSpy->getInvocations())
        );

        $this->assertEquals(1, $numberOfTimesSnippetWasAddedToPageBuilder, sprintf(
            'Failed to assert "%s" snippet with "%s" value was added to page builder.',
            $snippetCode,
            $snippetValue
        ));
    }

    /**
     * @return SearchEngineResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchEngineResponse()
    {
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);

        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('count')->willReturn(1);
        $stubSearchDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);

        $stubFacetFieldCollection = $this->getMock(FacetFieldCollection::class, [], [], '', false);

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getSearchDocuments')->willReturn($stubSearchDocumentCollection);
        $stubSearchEngineResponse->method('getFacetFieldCollection')->willReturn($stubFacetFieldCollection);

        return $stubSearchEngineResponse;
    }

    protected function setUp()
    {
        /** @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject $stubDataPoolReader */
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $stubDataPoolReader->method('getSnippets')->willReturn([]);

        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn('bar');

        /** @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGeneratorLocator */
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')
            ->willReturn($stubSnippetKeyGenerator);

        $this->mockPageBuilder = $this->createMockPageBuilder();
        $this->stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);

        $this->pageContentBuilder = new ProductListingPageContentBuilder(
            $stubDataPoolReader,
            $stubSnippetKeyGeneratorLocator,
            $this->mockPageBuilder,
            $this->stubSortOrderConfig
        );

        $this->stubPageMetaInfoSnippetContent = $this->getMock(PageMetaInfoSnippetContent::class);
        $this->stubContext = $this->getMock(Context::class);
        $this->stubProductsPerPage = $this->getMock(ProductsPerPage::class, [], [], '', false);
        $this->stubSelectedSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
        $this->stubSearchEngineResponse = $this->createStubSearchEngineResponse();
    }

    public function testPageIsBuilt()
    {
        $this->mockPageBuilder->expects($this->once())->method('buildPage');
        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );
    }

    public function testProductsInListingAreAddedToPageBuilder()
    {
        $productGridSnippetCode = 'product_grid';
        $productPricesSnippetCode = 'product_prices';

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($productGridSnippetCode);
        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($productPricesSnippetCode);
    }

    public function testFilterNavigationSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'filter_navigation';

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode);
    }

    public function testTotalNumberOfResultsSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'total_number_of_results';

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode);
    }

    public function testProductPerPageSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'products_per_page';

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode);
    }

    public function testInitialSortOrderConfigSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'sort_order_config';
        $selectedSortOrderConfigRepresentation = 'selected-sort-order-config';
        $initialSortOrderConfigRepresentation = 'initial-sort-order-config';

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);

        $this->stubSelectedSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSelectedSortOrderConfig->method('jsonSerialize')->willReturn($selectedSortOrderConfigRepresentation);

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSortOrderConfig->method('jsonSerialize')->willReturn($initialSortOrderConfigRepresentation);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $expectedSnippetValue = json_encode([$selectedSortOrderConfigRepresentation]);

        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, $expectedSnippetValue);
    }

    public function testUserSelectedSortOrderConfigSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'sort_order_config';
        $selectedSortOrderConfigRepresentation = 'selected-sort-order-config';
        $initialSortOrderConfigRepresentation = 'initial-sort-order-config';

        $stubAttributeCodeA = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCodeA->method('__toString')->willReturn('A');
        $stubAttributeCodeB = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCodeA->method('__toString')->willReturn('B');

        $this->stubSelectedSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCodeA);
        $this->stubSelectedSortOrderConfig->method('jsonSerialize')->willReturn($selectedSortOrderConfigRepresentation);

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCodeB);
        $this->stubSortOrderConfig->method('jsonSerialize')->willReturn($initialSortOrderConfigRepresentation);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $expectedSnippetValue = json_encode([$initialSortOrderConfigRepresentation]);

        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, $expectedSnippetValue);
    }

    public function testNewSortOrderConfigSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'sort_order_config';

        $stubAttributeCodeA = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCodeA->method('__toString')->willReturn('A');
        $stubAttributeCodeB = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCodeA->method('__toString')->willReturn('B');

        $stubSortOrderDirection = $this->getMock(SortOrderDirection::class, [], [], '', false);

        $this->stubSelectedSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCodeA);

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCodeB);
        $this->stubSortOrderConfig->method('getSelectedDirection')->willReturn($stubSortOrderDirection);
        $this->stubSortOrderConfig->method('isSelected')->willReturn(true);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $expectedSnippetValue = '[{"code":"","selectedDirection":null,"selected":false}]';

        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, $expectedSnippetValue);
    }
}
