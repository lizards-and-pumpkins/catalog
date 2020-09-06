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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 */
class ProductListingPageContentBuilderTest extends TestCase
{
    /**
     * @var PageBuilder|MockObject
     */
    private $mockPageBuilder;

    /**
     * @var ProductListingPageContentBuilder
     */
    private $pageContentBuilder;

    /**
     * @var PageMetaInfoSnippetContent
     */
    private $stubPageMetaInfoSnippetContent;

    /**
     * @var Context
     */
    private $stubContext;

    /**
     * @var string[]
     */
    private $stubKeyGeneratorParams = [];

    /**
     * @var ProductSearchResult
     */
    private $stubProductSearchResult;

    /**
     * @var ProductsPerPage
     */
    private $stubProductsPerPage;

    /**
     * @var SortBy
     */
    private $stubSelectedSortBy;

    /**
     * @var Translator
     */
    private $stubTranslator;

    /**
     * @var FacetFieldCollection|MockObject
     */
    private $stubFacetFieldCollection;

    /**
     * @var SearchFieldToRequestParamMap
     */
    private $stubSearchFieldToRequestParamMap;

    /**
     * @var SortBy[]
     */
    private $stubListOfAvailableSortBy;

    /**
     * @return ProductSearchResult
     */
    private function createStubProductSearchResult() : ProductSearchResult
    {
        $this->stubFacetFieldCollection = $this->createMock(FacetFieldCollection::class);

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $stubProductSearchResult->method('getFacetFieldCollection')->willReturn($this->stubFacetFieldCollection);

        return $stubProductSearchResult;
    }

    final protected function setUp(): void
    {
        $this->mockPageBuilder = $this->createMock(PageBuilder::class);

        $class = SearchFieldToRequestParamMap::class;
        $this->stubSearchFieldToRequestParamMap = $this->createMock($class);
        
        $this->stubTranslator = $this->createMock(Translator::class);

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

    public function testPageIsBuilt(): void
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

    public function testProductsAreAddedToPageBuilder(): void
    {
        $productGridSnippetCode = 'product_grid';

        $this->mockPageBuilder->expects($this->at(1))->method('addSnippetsToPage')
            ->with([$productGridSnippetCode => $productGridSnippetCode]);

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

    public function testFilterNavigationSnippetIsAddedToPageBuilder(): void
    {
        $snippetCode = 'filter_navigation';

        $this->mockPageBuilder->expects($this->at(0))->method('addSnippetsToPage')
            ->with([$snippetCode => $snippetCode]);

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

    public function testItMapsFilterNavigationFieldsToRequestParameterNames(): void
    {
        $snippetCode = 'filter_navigation';
        $dummyFacetData = ['price_with_tax' => ['a', 'b', 'c']];
        $expectedValue = json_encode(['price' => ['a', 'b', 'c']]);

        $this->mockPageBuilder->expects($this->at(0))->method('addSnippetsToPage')
            ->with([$snippetCode => $snippetCode], [$snippetCode => $expectedValue]);

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
    }

    public function testTotalNumberOfResultsSnippetIsAddedToPageBuilder(): void
    {
        $snippetCode = 'total_number_of_results';

        $this->mockPageBuilder->expects($this->at(2))->method('addSnippetsToPage')
            ->with([$snippetCode => $snippetCode]);

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

    public function testProductPerPageSnippetIsAddedToPageBuilder(): void
    {
        $snippetCode = 'products_per_page';

        $this->mockPageBuilder->expects($this->at(3))->method('addSnippetsToPage')
            ->with([$snippetCode => $snippetCode]);

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

    public function testAddsAvailableSortByListSnippetToPageBuilder(): void
    {
        $snippetCode = 'available_sort_orders';

        $this->mockPageBuilder->expects($this->at(4))->method('addSnippetsToPage')
            ->with([$snippetCode => $snippetCode]);

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

    public function testAddsDefaultSortBySnippetToPageBuilder(): void
    {
        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $selectedSortByRepresentation = ['selected-sort-by'];

        $snippetCode = 'selected_sort_order';
        $expectedValue = json_encode($selectedSortByRepresentation);

        $this->mockPageBuilder->expects($this->at(5))->method('addSnippetsToPage')
            ->with([$snippetCode => $snippetCode], [$snippetCode => $expectedValue]);

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
    }

    public function testAddsProductListingAttributesSnippetToPageBuilder(): void
    {
        $productListingAttributes = ['foo' => 'bar'];
        $this->stubPageMetaInfoSnippetContent->method('getPageSpecificData')->willReturn([
            'product_listing_attributes' => $productListingAttributes
        ]);

        $snippetCode = 'product_listing_attributes';
        $expectedValue = json_encode($productListingAttributes);

        $this->mockPageBuilder->expects($this->at(6))->method('addSnippetsToPage')
            ->with([$snippetCode => $snippetCode], [$snippetCode => $expectedValue]);

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

    public function testTranslationsAreAddedToPageBuilder(): void
    {
        $translations = ['foo' => 'bar'];

        $this->stubTranslator->method('jsonSerialize')->willReturn($translations);

        $snippetCode = 'translations';
        $expectedValue = json_encode($translations);

        $this->mockPageBuilder->expects($this->at(7))->method('addSnippetsToPage')
            ->with([$snippetCode => $snippetCode], [$snippetCode => $expectedValue]);

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
}
