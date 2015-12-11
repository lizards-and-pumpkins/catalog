<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\ContentDelivery\PageBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 */
class ProductListingPageContentBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $testSnippetCode = 'bar';

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject $stubDataPoolReader
     */
    private $stubDataPoolReader;

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
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubFacetFieldCollection = $this->getMock(FacetFieldCollection::class, [], [], '', false);

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([$stubProductId]);
        $stubSearchEngineResponse->method('getFacetFieldCollection')->willReturn($stubFacetFieldCollection);

        return $stubSearchEngineResponse;
    }

    protected function setUp()
    {
        $this->stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);

        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testSnippetCode);

        /** @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGeneratorLocator */
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')
            ->willReturn($stubSnippetKeyGenerator);

        $this->mockPageBuilder = $this->createMockPageBuilder();
        $this->stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);

        $this->pageContentBuilder = new ProductListingPageContentBuilder(
            $this->stubDataPoolReader,
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
        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);
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
        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $productGridSnippetCode = 'product_grid';

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($productGridSnippetCode);
    }

    public function testPricesAreAddedToPageBuilder()
    {
        $testSnippetContent = 'baz';
        $this->stubDataPoolReader->method('getSnippets')->willReturn([$this->testSnippetCode => $testSnippetContent]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $productPricesSnippetCode = 'product_prices';
        $expectedSnippetContents = json_encode([[$testSnippetContent, $testSnippetContent]]);

        $this->assertDynamicSnippetWasAddedToPageBuilder($productPricesSnippetCode, $expectedSnippetContents);
    }

    public function testFilterNavigationSnippetIsAddedToPageBuilder()
    {
        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $snippetCode = 'filter_navigation';

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode);
    }

    public function testTotalNumberOfResultsSnippetIsAddedToPageBuilder()
    {
        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $snippetCode = 'total_number_of_results';

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode);
    }

    public function testProductPerPageSnippetIsAddedToPageBuilder()
    {
        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $snippetCode = 'products_per_page';

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode);
    }

    public function testInitialSortOrderConfigSnippetIsAddedToPageBuilder()
    {
        $selectedSortOrderConfigRepresentation = 'selected-sort-order-config';
        $initialSortOrderConfigRepresentation = 'initial-sort-order-config';

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);

        $this->stubSelectedSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSelectedSortOrderConfig->method('jsonSerialize')->willReturn($selectedSortOrderConfigRepresentation);

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSortOrderConfig->method('jsonSerialize')->willReturn($initialSortOrderConfigRepresentation);

        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $snippetCode = 'sort_order_config';
        $expectedSnippetValue = json_encode([$selectedSortOrderConfigRepresentation]);

        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, $expectedSnippetValue);
    }

    public function testUserSelectedSortOrderConfigSnippetIsAddedToPageBuilder()
    {
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

        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $snippetCode = 'sort_order_config';
        $expectedSnippetValue = json_encode([$initialSortOrderConfigRepresentation]);

        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, $expectedSnippetValue);
    }

    public function testNewSortOrderConfigSnippetIsAddedToPageBuilder()
    {
        $stubAttributeCodeA = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCodeA->method('__toString')->willReturn('A');
        $stubAttributeCodeB = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCodeA->method('__toString')->willReturn('B');

        $stubSortOrderDirection = $this->getMock(SortOrderDirection::class, [], [], '', false);

        $this->stubSelectedSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCodeA);

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCodeB);
        $this->stubSortOrderConfig->method('getSelectedDirection')->willReturn($stubSortOrderDirection);
        $this->stubSortOrderConfig->method('isSelected')->willReturn(true);

        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $snippetCode = 'sort_order_config';
        $expectedSnippetValue = '[{"code":"","selectedDirection":null,"selected":false}]';

        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, $expectedSnippetValue);
    }
}
