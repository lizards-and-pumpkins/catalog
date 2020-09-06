<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\ProductSearch\Exception\InvalidNumberOfProductsPerPageException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\QueryOptions
 */
class QueryOptionsTest extends TestCase
{
    private $testFilterSelection = ['foo' => ['bar', 'baz']];

    /**
     * @var Context|MockObject
     */
    private $stubContext;

    /**
     * @var FacetFiltersToIncludeInResult|MockObject
     */
    private $stubFacetFiltersToIncludeInResult;

    private $testRowsPerPage = 10;

    private $testPageNumber = 1;

    /**
     * @var SortBy|MockObject
     */
    private $stubSearchOrderConfig;

    /**
     * @var QueryOptions
     */
    private $queryOptions;

    final protected function setUp(): void
    {
        $this->stubContext = $this->createMock(Context::class);
        $this->stubFacetFiltersToIncludeInResult = $this->createMock(FacetFiltersToIncludeInResult::class);
        $this->stubSearchOrderConfig = $this->createMock(SortBy::class);

        $this->queryOptions = QueryOptions::create(
            $this->testFilterSelection,
            $this->stubContext,
            $this->stubFacetFiltersToIncludeInResult,
            $this->testRowsPerPage,
            $this->testPageNumber,
            $this->stubSearchOrderConfig
        );
    }

    public function testExceptionIsThrownIfRowsPerPageIsNotAnInteger(): void
    {
        $this->expectException(\TypeError::class);

        $invalidRowsPerPage = 'foo';
        $this->queryOptions = QueryOptions::create(
            $this->testFilterSelection,
            $this->stubContext,
            $this->stubFacetFiltersToIncludeInResult,
            $invalidRowsPerPage,
            $this->testPageNumber,
            $this->stubSearchOrderConfig
        );
    }

    public function testExceptionIsThrownIfRowsPerPageIsNotPositive(): void
    {
        $invalidRowsPerPage = 0;

        $this->expectException(InvalidNumberOfProductsPerPageException::class);
        $this->expectExceptionMessage(
            sprintf('Number of rows per page must be positive, got "%s".', $invalidRowsPerPage)
        );

        $this->queryOptions = QueryOptions::create(
            $this->testFilterSelection,
            $this->stubContext,
            $this->stubFacetFiltersToIncludeInResult,
            $invalidRowsPerPage,
            $this->testPageNumber,
            $this->stubSearchOrderConfig
        );
    }

    public function testExceptionIsThrownIfCurrentPageNumberIsNotAnInteger(): void
    {
        $this->expectException(\TypeError::class);

        $invalidPageNumber = 'foo';
        $this->queryOptions = QueryOptions::create(
            $this->testFilterSelection,
            $this->stubContext,
            $this->stubFacetFiltersToIncludeInResult,
            $this->testRowsPerPage,
            $invalidPageNumber,
            $this->stubSearchOrderConfig
        );
    }


    public function testExceptionIsThrownIfPageNumberIsNegative(): void
    {
        $invalidPageNumber = -1;

        $this->expectException(InvalidNumberOfProductsPerPageException::class);
        $this->expectExceptionMessage(
            sprintf('Current page number can not be negative, got "%s".', $invalidPageNumber)
        );

        $this->queryOptions = QueryOptions::create(
            $this->testFilterSelection,
            $this->stubContext,
            $this->stubFacetFiltersToIncludeInResult,
            $this->testRowsPerPage,
            $invalidPageNumber,
            $this->stubSearchOrderConfig
        );
    }

    public function testFilterSelectionIsReturned(): void
    {
        $this->assertSame($this->testFilterSelection, $this->queryOptions->getFilterSelection());
    }

    public function testContextIsReturned(): void
    {
        $this->assertSame($this->stubContext, $this->queryOptions->getContext());
    }

    public function testFacetFiltersToIncludeInResultAreReturned(): void
    {
        $this->assertSame(
            $this->stubFacetFiltersToIncludeInResult,
            $this->queryOptions->getFacetFiltersToIncludeInResult()
        );
    }

    public function testNumberOfRowsPerPageIsReturned(): void
    {
        $this->assertSame($this->testRowsPerPage, $this->queryOptions->getRowsPerPage());
    }

    public function testCurrentPageNumberIsReturned(): void
    {
        $this->assertSame($this->testPageNumber, $this->queryOptions->getPageNumber());
    }

    public function testSortByIsReturned(): void
    {
        $this->assertSame($this->stubSearchOrderConfig, $this->queryOptions->getSortBy());
    }
}
