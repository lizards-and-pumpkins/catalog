<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\Context\Context;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\QueryOptions
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
        $this->stubContext = $this->getMock(Context::class);
        $this->stubFacetFiltersToIncludeInResult = $this->getMockWithoutInvokingTheOriginalConstructor(
            FacetFiltersToIncludeInResult::class
        );
        $this->stubSearchOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);

        $this->queryOptions = new QueryOptions(
            $this->testFilterSelection,
            $this->stubContext,
            $this->stubFacetFiltersToIncludeInResult,
            $this->testRowsPerPage,
            $this->testPageNumber,
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
