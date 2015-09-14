<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Pagination
 */
class PaginationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var int
     */
    private $testCollectionSize = 20;

    /**
     * @var int
     */
    private $testNumberOfItemsPerPage = 9;

    /**
     * @var Pagination
     */
    private $pagination;

    protected function setUp()
    {
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);

        $this->pagination = Pagination::create(
            $this->stubRequest,
            $this->testCollectionSize,
            $this->testNumberOfItemsPerPage
        );
    }

    public function testExceptionIsThrowIfCollectionSizeIsNotInteger()
    {
        $this->setExpectedException(InvalidCollectionSizeTypeException::class);
        $invalidCollectionSize = [];
        Pagination::create($this->stubRequest, $invalidCollectionSize, $this->testNumberOfItemsPerPage);
    }

    public function testExceptionIsThrowIfNumberOfItemsPerPageIsNotInteger()
    {
        $this->setExpectedException(InvalidNumberOfItemsPerPageTypeException::class);
        $invalidNumberOfItemsPerPage = [];
        Pagination::create($this->stubRequest, $this->testCollectionSize, $invalidNumberOfItemsPerPage);
    }

    public function testCollectionSizeIsReturned()
    {
        $this->assertSame($this->testCollectionSize, $this->pagination->getCollectionSize());
    }

    public function testNumberOfItemsPerPageIsReturned()
    {
        $this->assertSame($this->testNumberOfItemsPerPage, $this->pagination->getNumberOfItemsPerPage());
    }

    public function testCurrentPageNumberIsReturned()
    {
        $testCurrentPageNumber = 2;

        $this->stubRequest->method('getQueryParameter')->with(Pagination::PAGINATION_QUERY_PARAMETER_NAME)
            ->willReturn($testCurrentPageNumber);

        $this->assertSame($testCurrentPageNumber, $this->pagination->getCurrentPageNumber());
    }

    public function testCurrentPageEqualsToOneByDefault()
    {
        $this->assertSame(1, $this->pagination->getCurrentPageNumber());
    }

    public function testQueryStringForGivenPageNumberIsReturned()
    {
        $pageNumber = 2;

        $this->stubRequest->method('getQueryParametersExceptGiven')->with(Pagination::PAGINATION_QUERY_PARAMETER_NAME)
            ->willReturn([]);

        $expectedQueryString = sprintf('%s=%d', Pagination::PAGINATION_QUERY_PARAMETER_NAME, $pageNumber);
        $result = $this->pagination->getQueryStringForPage($pageNumber);

        $this->assertEquals($expectedQueryString, $result);
    }
}
