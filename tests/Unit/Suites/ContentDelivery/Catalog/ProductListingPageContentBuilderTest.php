<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\ContentDelivery\PageBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Renderer\Translation\Translator;
use LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry;

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

    /**
     * @var Translator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubTranslator;

    /**
     * @var FacetFieldCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFieldCollection;

    /**
     * @var ProductJsonService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductJsonService;

    /**
     * @var SearchFieldToRequestParamMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchFieldToRequestParamMap;

    /**
     * @return PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
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
        $this->stubFacetFieldCollection = $this->getMock(FacetFieldCollection::class, [], [], '', false);

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([$stubProductId]);
        $stubSearchEngineResponse->method('getFacetFieldCollection')->willReturn($this->stubFacetFieldCollection);

        return $stubSearchEngineResponse;
    }

    protected function setUp()
    {
        $this->stubProductJsonService = $this->getMock(ProductJsonService::class, [], [], '', false);
        $this->mockPageBuilder = $this->createMockPageBuilder();

        $class = SearchFieldToRequestParamMap::class;
        $this->stubSearchFieldToRequestParamMap = $this->getMock($class, [], [], '', false);
        
        $this->stubTranslator = $this->getMock(Translator::class);

        /** @var TranslatorRegistry|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->getMock(TranslatorRegistry::class);
        $stubTranslatorRegistry->method('getTranslator')->willReturn($this->stubTranslator);

        $this->stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);

        $this->pageContentBuilder = new ProductListingPageContentBuilder(
            $this->stubProductJsonService,
            $this->mockPageBuilder,
            $this->stubSearchFieldToRequestParamMap,
            $stubTranslatorRegistry,
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
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

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
        $this->stubProductJsonService->method('get')->willReturn([]);
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

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

    public function testFilterNavigationSnippetIsAddedToPageBuilder()
    {
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

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

    public function testItMapsFilterNavigationFieldsToRequestParameterNames()
    {
        $dummyFacetData = [
            'price_with_tax' => ['a', 'b', 'c']
        ];
        $expectedValue = [
            'price' => ['a', 'b', 'c']
        ];
        
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);
        $this->stubFacetFieldCollection->method('jsonSerialize')->willReturn($dummyFacetData);
        $this->stubSearchFieldToRequestParamMap->method('getQueryParameterName')
            ->with('price_with_tax')->willReturn('price');

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $snippetCode = 'filter_navigation';
        
        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, json_encode($expectedValue));
    }

    public function testTotalNumberOfResultsSnippetIsAddedToPageBuilder()
    {
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

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
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

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

        $this->stubProductJsonService->method('get')->willReturn([]);
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

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

        $this->stubProductJsonService->method('get')->willReturn([]);
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

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
        $stubSortOrderDirection->method('__toString')->willReturn(SortOrderDirection::ASC);

        $this->stubSelectedSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCodeA);

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCodeB);
        $this->stubSortOrderConfig->method('getSelectedDirection')->willReturn($stubSortOrderDirection);
        $this->stubSortOrderConfig->method('isSelected')->willReturn(true);

        $this->stubProductJsonService->method('get')->willReturn([]);
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $snippetCode = 'sort_order_config';
        $expectedSnippetValue = '[{"code":"","selectedDirection":"asc","selected":false}]';

        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, $expectedSnippetValue);
    }

    public function testTranslationsAreAddedToPageBuilder()
    {
        $translations = ['foo' => 'bar'];

        $this->stubTranslator->method('jsonSerialize')->willReturn($translations);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortOrderConfig
        );

        $snippetCode = 'translations';
        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, json_encode($translations));
    }
}
