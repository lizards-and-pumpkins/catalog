<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 */
class ProductListingPageContentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var SortBy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSortBy;

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
     * @var SortBy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSelectedSortBy;

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
    private function createMockPageBuilder() : PageBuilder
    {
        $mockPageBuilder = $this->createMock(PageBuilder::class);

        $this->addSnippetsToPageSpy = $this->any();
        $mockPageBuilder->expects($this->addSnippetsToPageSpy)->method('addSnippetsToPage');

        return $mockPageBuilder;
    }

    private function assertDynamicSnippetWithAnyValueWasAddedToPageBuilder(string $snippetCode)
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

    private function assertDynamicSnippetWasAddedToPageBuilder(string $snippetCode, string $snippetValue)
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
    private function createStubSearchEngineResponse() : SearchEngineResponse
    {
        $stubProductId = $this->createMock(ProductId::class);
        $this->stubFacetFieldCollection = $this->createMock(FacetFieldCollection::class);

        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([$stubProductId]);
        $stubSearchEngineResponse->method('getFacetFieldCollection')->willReturn($this->stubFacetFieldCollection);

        return $stubSearchEngineResponse;
    }

    protected function setUp()
    {
        $this->stubProductJsonService = $this->createMock(ProductJsonService::class);
        $this->mockPageBuilder = $this->createMockPageBuilder();

        $class = SearchFieldToRequestParamMap::class;
        $this->stubSearchFieldToRequestParamMap = $this->createMock($class);
        
        $this->stubTranslator = $this->createMock(Translator::class);

        /** @var TranslatorRegistry|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->createMock(TranslatorRegistry::class);
        $stubTranslatorRegistry->method('getTranslator')->willReturn($this->stubTranslator);

        $this->stubSortBy = $this->createMock(SortBy::class);

        $this->pageContentBuilder = new ProductListingPageContentBuilder(
            $this->stubProductJsonService,
            $this->mockPageBuilder,
            $this->stubSearchFieldToRequestParamMap,
            $stubTranslatorRegistry,
            $this->stubSortBy
        );

        $this->stubPageMetaInfoSnippetContent = $this->createMock(PageMetaInfoSnippetContent::class);
        $this->stubContext = $this->createMock(Context::class);
        $this->stubProductsPerPage = $this->createMock(ProductsPerPage::class);
        $this->stubSelectedSortBy = $this->createMock(SortBy::class);
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
            $this->stubSelectedSortBy
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
            $this->stubSelectedSortBy
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
            $this->stubSelectedSortBy
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
            $this->stubSelectedSortBy
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
            $this->stubSelectedSortBy
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
            $this->stubSelectedSortBy
        );

        $snippetCode = 'products_per_page';

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode);
    }

    public function testInitialSortBySnippetIsAddedToPageBuilder()
    {
        $selectedSortByRepresentation = ['selected-sort-order-config'];
        $initialSortByRepresentation = ['initial-sort-order-config'];

        $stubAttributeCode = $this->createMock(AttributeCode::class);

        $this->stubSelectedSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSelectedSortBy->method('jsonSerialize')->willReturn($selectedSortByRepresentation);

        $this->stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSortBy->method('jsonSerialize')->willReturn($initialSortByRepresentation);

        $this->stubProductJsonService->method('get')->willReturn([]);
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy
        );

        $snippetCode = 'sort_order_config';
        $expectedSnippetValue = json_encode([$selectedSortByRepresentation]);

        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, $expectedSnippetValue);
    }

    public function testUserSelectedSortBySnippetIsAddedToPageBuilder()
    {
        $selectedSortByRepresentation = ['selected-sort-order-config'];
        $initialSortByRepresentation = ['initial-sort-order-config'];

        $stubAttributeCodeA = $this->createMock(AttributeCode::class);
        $stubAttributeCodeA->method('__toString')->willReturn('A');
        $stubAttributeCodeB = $this->createMock(AttributeCode::class);
        $stubAttributeCodeA->method('__toString')->willReturn('B');

        $this->stubSelectedSortBy->method('getAttributeCode')->willReturn($stubAttributeCodeA);
        $this->stubSelectedSortBy->method('jsonSerialize')->willReturn($selectedSortByRepresentation);

        $this->stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCodeB);
        $this->stubSortBy->method('jsonSerialize')->willReturn($initialSortByRepresentation);

        $this->stubProductJsonService->method('get')->willReturn([]);
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy
        );

        $snippetCode = 'sort_order_config';
        $expectedSnippetValue = json_encode([$initialSortByRepresentation]);

        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, $expectedSnippetValue);
    }

    public function testNewSortBySnippetIsAddedToPageBuilder()
    {
        $stubAttributeCodeA = $this->createMock(AttributeCode::class);
        $stubAttributeCodeA->method('__toString')->willReturn('A');
        $stubAttributeCodeB = $this->createMock(AttributeCode::class);
        $stubAttributeCodeA->method('__toString')->willReturn('B');

        $stubSortDirection = $this->createMock(SortDirection::class);
        $stubSortDirection->method('__toString')->willReturn(SortDirection::ASC);

        $this->stubSelectedSortBy->method('getAttributeCode')->willReturn($stubAttributeCodeA);

        $this->stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCodeB);
        $this->stubSortBy->method('getSelectedDirection')->willReturn($stubSortDirection);
        $this->stubSortBy->method('isSelected')->willReturn(true);

        $this->stubProductJsonService->method('get')->willReturn([]);
        $this->stubFacetFieldCollection->method('getFacetFields')->willReturn([]);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy
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
            $this->stubSelectedSortBy
        );

        $snippetCode = 'translations';
        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, json_encode($translations));
    }

    public function testAddsTheRobotsMetaTagSnippetToHeadContainer()
    {
        $this->mockPageBuilder->expects($this->once())
            ->method('addSnippetToContainer')
            ->with('head_container', ProductListingRobotsMetaTagSnippetRenderer::CODE);
            
        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubSearchEngineResponse,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy
        );
    }
}
