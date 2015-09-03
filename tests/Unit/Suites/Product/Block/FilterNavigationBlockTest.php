<?php

namespace Brera\Product\Block;

use Brera\Product\FilterNavigationFilterCollection;
use Brera\Renderer\Block;
use Brera\Renderer\BlockRenderer;
use Brera\Renderer\InvalidDataObjectException;

/**
 * @covers \Brera\Product\Block\FilterNavigationBlock
 * @uses   \Brera\Renderer\Block
 */
class FilterNavigationBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterNavigationBlock
     */
    private $block;

    /**
     * @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubBlockRenderer;

    protected function setUp()
    {
        $this->stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $stubDataObject = $this->getMock(FilterNavigationFilterCollection::class, [], [], '', false);
        $this->block = new FilterNavigationBlock($this->stubBlockRenderer, 'foo.phtml', 'foo', $stubDataObject);
    }

    public function testBlockClassIsExtended()
    {
        $this->assertInstanceOf(Block::class, $this->block);
    }

    public function testExceptionIsThrownIfDataObjectIsNotFilterNavigationFilterCollection()
    {
        $this->setExpectedException(
            InvalidDataObjectException::class,
            sprintf('Data object must be instance of %s, got "stdClass".', FilterNavigationFilterCollection::class)
        );
        $invalidDataObject = new \stdClass;
        $block = new FilterNavigationBlock($this->stubBlockRenderer, 'foo.phtml', 'foo', $invalidDataObject);
        $block->getFilterCollection();
    }

    public function testFilterNavigationFilterCollectionIsReturned()
    {
        $result = $this->block->getFilterCollection();
        $this->assertInstanceOf(FilterNavigationFilterCollection::class, $result);
    }
}
