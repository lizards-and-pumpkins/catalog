<?php

namespace Brera;

use Brera\Http\HttpRequest;

/**
 * @covers \Brera\PaginationData
 */
class PaginationDataTest extends \PHPUnit_Framework_TestCase
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
     * @var PaginationData
     */
    private $paginationData;

    protected function setUp()
    {
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);

        $this->paginationData = PaginationData::create(
            $this->stubRequest,
            $this->testCollectionSize,
            $this->testNumberOfItemsPerPage
        );
    }

    public function testExceptionIsThrowIfCollectionSizeIsNotInteger()
    {
        $this->setExpectedException(InvalidCollectionSizeTypeException::class);
        $invalidCollectionSize = [];
        PaginationData::create($this->stubRequest, $invalidCollectionSize, $this->testNumberOfItemsPerPage);
    }

    public function testExceptionIsThrowIfNumberOfItemsPerPageIsNotInteger()
    {
        $this->setExpectedException(InvalidNumberOfItemsPerPageTypeException::class);
        $invalidNumberOfItemsPerPage = [];
        PaginationData::create($this->stubRequest, $this->testCollectionSize, $invalidNumberOfItemsPerPage);
    }

    public function testHttpRequestIsReturned()
    {
        $this->assertSame($this->stubRequest, $this->paginationData->getRequest());
    }

    public function testCollectionSizeIsReturned()
    {
        $this->assertSame($this->testCollectionSize, $this->paginationData->getCollectionSize());
    }

    public function testNumberOfItemsPerPageIsReturned()
    {
        $this->assertSame($this->testNumberOfItemsPerPage, $this->paginationData->getNumberOfItemsPerPage());
    }
}
