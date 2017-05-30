<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchResult;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 */
class ProductListingPageContentBuilderTest extends TestCase
{
    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

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
     * @var ProductSearchResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductSearchResult;

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
     * @var SearchFieldToRequestParamMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchFieldToRequestParamMap;

    /**
     * @var SortBy[]
     */
    private $stubListOfAvailableSortBy;

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
            return (int) ([$snippetCode => $snippetCode] === $invocation->parameters[0]);
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
                return (int) ([$snippetCode => $snippetCode] === $invocation->parameters[0] &&
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
     * @return ProductSearchResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductSearchResult() : ProductSearchResult
    {
        $this->stubFacetFieldCollection = $this->createMock(FacetFieldCollection::class);

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $stubProductSearchResult->method('getFacetFieldCollection')->willReturn($this->stubFacetFieldCollection);

        return $stubProductSearchResult;
    }

    protected function setUp()
    {
        $this->mockPageBuilder = $this->createMockPageBuilder();

        $class = SearchFieldToRequestParamMap::class;
        $this->stubSearchFieldToRequestParamMap = $this->createMock($class);
        
        $this->stubTranslator = $this->createMock(Translator::class);

        /** @var TranslatorRegistry|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->createMock(TranslatorRegistry::class);
        $stubTranslatorRegistry->method('getTranslator')->willReturn($this->stubTranslator);

        $this->pageContentBuilder = new ProductListingPageContentBuilder(
            $this->mockPageBuilder,
            $this->stubSearchFieldToRequestParamMap,
            $stubTranslatorRegistry
        );

        $this->stubPageMetaInfoSnippetContent = $this->createMock(PageMetaInfoSnippetContent::class);
        $this->stubContext = $this->createMock(Context::class);
        $this->stubProductsPerPage = $this->createMock(ProductsPerPage::class);
        $this->stubSelectedSortBy = $this->createMock(SortBy::class);
        $this->stubListOfAvailableSortBy = [$this->createMock(SortBy::class)];
        $this->stubProductSearchResult = $this->createStubProductSearchResult();
    }

    public function testPageIsBuilt()
    {
        $this->mockPageBuilder->expects($this->once())->method('buildPage');

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubProductSearchResult,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy,
            ...$this->stubListOfAvailableSortBy
        );
    }

    public function testProductsInListingAreAddedToPageBuilder()
    {
        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubProductSearchResult,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy,
            ...$this->stubListOfAvailableSortBy
        );

        $productGridSnippetCode = 'product_grid';

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($productGridSnippetCode);
    }

    public function testFilterNavigationSnippetIsAddedToPageBuilder()
    {
        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubProductSearchResult,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy,
            ...$this->stubListOfAvailableSortBy
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
        
        $this->stubFacetFieldCollection->method('jsonSerialize')->willReturn($dummyFacetData);
        $this->stubSearchFieldToRequestParamMap->method('getQueryParameterName')
            ->with('price_with_tax')->willReturn('price');

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubProductSearchResult,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy,
            ...$this->stubListOfAvailableSortBy
        );

        $snippetCode = 'filter_navigation';
        
        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, json_encode($expectedValue));
    }

    public function testTotalNumberOfResultsSnippetIsAddedToPageBuilder()
    {
        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubProductSearchResult,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy,
            ...$this->stubListOfAvailableSortBy
        );

        $snippetCode = 'total_number_of_results';

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode);
    }

    public function testProductPerPageSnippetIsAddedToPageBuilder()
    {
        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubProductSearchResult,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy,
            ...$this->stubListOfAvailableSortBy
        );

        $snippetCode = 'products_per_page';

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode);
    }

    public function testAddsAvailableSortByListSnippetToPageBuilder()
    {
        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubProductSearchResult,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy,
            ...$this->stubListOfAvailableSortBy
        );

        $snippetCode = 'available_sort_orders';

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($snippetCode);
    }

    public function testAddsDefaultSortBySnippetToPageBuilder()
    {
        $selectedSortByRepresentation = ['selected-sort-by'];

        $stubAttributeCode = $this->createMock(AttributeCode::class);

        $this->stubSelectedSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSelectedSortBy->method('jsonSerialize')->willReturn($selectedSortByRepresentation);

        $this->pageContentBuilder->buildPageContent(
            $this->stubPageMetaInfoSnippetContent,
            $this->stubContext,
            $this->stubKeyGeneratorParams,
            $this->stubProductSearchResult,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy,
            ...$this->stubListOfAvailableSortBy
        );

        $snippetCode = 'selected_sort_order';
        $expectedSnippetValue = json_encode($selectedSortByRepresentation);

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
            $this->stubProductSearchResult,
            $this->stubProductsPerPage,
            $this->stubSelectedSortBy,
            ...$this->stubListOfAvailableSortBy
        );

        $snippetCode = 'translations';
        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, json_encode($translations));
    }
}
