<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\ProductListing\Exception\InvalidNumberOfProductsPerPageException;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\QueryOptions
 */
class QueryOptionsTest extends \PHPUnit_Framework_TestCase
{
    private $testFilterSelection = ['foo' => ['bar', 'baz']];

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var FacetFiltersToIncludeInResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFiltersToIncludeInResult;

    private $testRowsPerPage = 10;

    private $testPageNumber = 1;

    /**
     * @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchOrderConfig;

    /**
     * @var QueryOptions
     */
    private $queryOptions;

    protected function setUp()
    {
        $this->stubContext = $this->createMock(Context::class);
        $this->stubFacetFiltersToIncludeInResult = $this->createMock(FacetFiltersToIncludeInResult::class);
        $this->stubSearchOrderConfig = $this->createMock(SortOrderConfig::class);

        $this->queryOptions = QueryOptions::create(
            $this->testFilterSelection,
            $this->stubContext,
            $this->stubFacetFiltersToIncludeInResult,
            $this->testRowsPerPage,
            $this->testPageNumber,
            $this->stubSearchOrderConfig
        );
    }

    public function testExceptionIsThrownIfRowsPerPageIsNotAnInteger()
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

    public function testExceptionIsThrownIfRowsPerPageIsNotPositive()
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

    public function testExceptionIsThrownIfCurrentPageNumberIsNotAnInteger()
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


    public function testExceptionIsThrownIfPageNumberIsNegative()
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

    public function testFilterSelectionIsReturned()
    {
        $this->assertSame($this->testFilterSelection, $this->queryOptions->getFilterSelection());
    }

    public function testContextIsReturned()
    {
        $this->assertSame($this->stubContext, $this->queryOptions->getContext());
    }

    public function testFacetFiltersToIncludeInResultAreReturned()
    {
        $this->assertSame(
            $this->stubFacetFiltersToIncludeInResult,
            $this->queryOptions->getFacetFiltersToIncludeInResult()
        );
    }

    public function testNumberOfRowsPerPageIsReturned()
    {
        $this->assertSame($this->testRowsPerPage, $this->queryOptions->getRowsPerPage());
    }

    public function testCurrentPageNumberIsReturned()
    {
        $this->assertSame($this->testPageNumber, $this->queryOptions->getPageNumber());
    }

    public function testSortOrderConfigIsReturned()
    {
        $this->assertSame($this->stubSearchOrderConfig, $this->queryOptions->getSortOrderConfig());
    }
}
