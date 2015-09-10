<?php

namespace Brera;

use Brera\Renderer\Block;
use Brera\Renderer\BlockRenderer;
use Brera\Renderer\InvalidDataObjectException;

/**
 * @covers \Brera\PaginationBlock
 * @uses   \Brera\Renderer\Block
 */
class PaginationBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pagination|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataObject;

    /**
     * @var PaginationBlock
     */
    private $block;

    protected function setUp()
    {
        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $this->stubDataObject = $this->getMock(Pagination::class, [], [], '', false);

        $this->block = new PaginationBlock($stubBlockRenderer, 'foo.phtml', 'foo', $this->stubDataObject);
    }

    public function testBlockClassIsExtended()
    {
        $this->assertInstanceOf(Block::class, $this->block);
    }

    public function testExceptionIsThrownIfDataObjectIsNotInstanceOfPaginationData()
    {
        $this->setExpectedException(
            InvalidDataObjectException::class,
            'Data object must be instance of PaginationData, got "array".'
        );

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $blockName = 'foo';
        $invalidDataObject = [];

        $block = new PaginationBlock($stubBlockRenderer, 'foo.phtml', $blockName, $invalidDataObject);
        $block->getTotalPageCount();
    }

    public function testTotalPagesCountIsReturned()
    {
        $testCollectionSize = 20;
        $testNumberOfItemsPerPage = 9;

        $this->stubDataObject->method('getCollectionSize')->willReturn($testCollectionSize);
        $this->stubDataObject->method('getNumberOfItemsPerPage')->willReturn($testNumberOfItemsPerPage);

        $expectedNumberOfProductsPerPage = ceil($testCollectionSize / $testNumberOfItemsPerPage);
        $this->assertEquals($expectedNumberOfProductsPerPage, $this->block->getTotalPageCount());
    }

    public function testRetrievingOfCurrentPageNumberIsDelegatedToPagination()
    {
        $testCurrentPageNumber = 2;
        $this->stubDataObject->method('getCurrentPageNumber')->willReturn($testCurrentPageNumber);

        $this->assertEquals($testCurrentPageNumber, $this->block->getCurrentPageNumber());
    }

    public function testRetrievingOfQueryStringForGivenPageNumberIsDelegatedToPagination()
    {
        $pageNumber = 2;
        $expectedQueryString = sprintf('%s=%d', Pagination::PAGINATION_QUERY_PARAMETER_NAME, $pageNumber);
        $this->stubDataObject->method('getQueryStringForPage')->with($pageNumber)->willReturn($expectedQueryString);

        $this->assertEquals($expectedQueryString, $this->block->getQueryStringForPage($pageNumber));
    }
}
