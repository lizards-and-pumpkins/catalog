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

    /**
     * @var FilterNavigationFilterCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFilterCollection;

    protected function setUp()
    {
        $this->stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $this->stubFilterCollection = $this->getMock(FilterNavigationFilterCollection::class, [], [], '', false);
        $stubDataObject = $this->stubFilterCollection;

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
        $blockName = 'foo';
        $block = new FilterNavigationBlock($this->stubBlockRenderer, 'foo.phtml', $blockName, $invalidDataObject);
        $block->getFilterCollection();
    }

    public function testFilterNavigationFilterCollectionIsReturned()
    {
        $result = $this->block->getFilterCollection();
        $this->assertInstanceOf(FilterNavigationFilterCollection::class, $result);
    }

    public function testQueryStringContainsOnlyGivenFilterIfNoFiltersAreCurrentlySelected()
    {
        $filterCode = 'foo';
        $filterValue = 'bar';

        $this->stubFilterCollection->method('getSelectedFilters')->willReturn([$filterCode => []]);

        $result = $this->block->getQueryStringForFilterSelection($filterCode, $filterValue);
        $expectedQueryString = sprintf('%s=%s', $filterCode, $filterValue);

        $this->assertEquals($expectedQueryString, $result);
    }
    
    public function testQueryStringContainsAllSelectedFiltersPlusGivenFilter()
    {
        $newFilterCode = 'foo';
        $newFilterValue = 'bar';

        $existingFilterCode = 'baz';
        $existingFilterSelectedValue = 'qux';

        $this->stubFilterCollection->method('getSelectedFilters')
            ->willReturn([$existingFilterCode => [$existingFilterSelectedValue], $newFilterCode => []]);

        $result = $this->block->getQueryStringForFilterSelection($newFilterCode, $newFilterValue);
        $resultTokensArray = explode('&', $result);

        $expectedOldToken = sprintf('%s=%s', $existingFilterCode, $existingFilterSelectedValue);
        $expectedNewToken = sprintf('%s=%s', $newFilterCode, $newFilterValue);

        $this->assertCount(2, $resultTokensArray);
        $this->assertContains($expectedOldToken, $resultTokensArray);
        $this->assertContains($expectedNewToken, $resultTokensArray);
    }

    public function testSelectedValueShouldBeAddedToPreviouslySelectedValuesOfAFilter()
    {
        $filterCode = 'foo';
        $filterValue = 'bar';
        $filterPreviouslySelectedValue = 'baz';

        $this->stubFilterCollection->method('getSelectedFilters')
            ->willReturn([$filterCode => [$filterPreviouslySelectedValue]]);

        $result = $this->block->getQueryStringForFilterSelection($filterCode, $filterValue);
        $expectedQueryString = sprintf(
            '%s=%s%s%s',
            $filterCode,
            $filterPreviouslySelectedValue,
            urlencode(FilterNavigationBlock::VALUES_SEPARATOR),
            $filterValue
        );

        $this->assertSame($expectedQueryString, $result);
    }
    
    public function testFilterValueIsRemovedFromQueryStringIfPreviouslySelected()
    {
        $filterCode = 'foo';
        $filterValue = 'bar';
        $filterOtherSelectedValue = 'some-other-value-which-should-not-be-removed';

        $otherFilterCode = 'baz';
        $otherFilterSelectedValue = 'qux';

        $this->stubFilterCollection->method('getSelectedFilters')->willReturn([
            $otherFilterCode => [$otherFilterSelectedValue],
            $filterCode      => [$filterValue, $filterOtherSelectedValue]
        ]);

        $result = $this->block->getQueryStringForFilterSelection($filterCode, $filterValue);
        $resultTokensArray = explode('&', $result);

        $expectedFilterToken = sprintf('%s=%s', $filterCode, $filterOtherSelectedValue);
        $expectedOtherFilterToken = sprintf('%s=%s', $otherFilterCode, $otherFilterSelectedValue);

        $this->assertCount(2, $resultTokensArray);
        $this->assertContains($expectedOtherFilterToken, $resultTokensArray);
        $this->assertContains($expectedFilterToken, $resultTokensArray);
    }

    public function testFilterIsRemovedFromQueryStringIfLastSelectedValueWasUnset()
    {
        $filterCode = 'foo';
        $filterValue = 'bar';

        $otherFilterCode = 'baz';
        $otherFilterSelectedValue = 'qux';

        $this->stubFilterCollection->method('getSelectedFilters')->willReturn([
            $otherFilterCode => [$otherFilterSelectedValue],
            $filterCode      => [$filterValue]
        ]);

        $result = $this->block->getQueryStringForFilterSelection($filterCode, $filterValue);
        $expectedQueryString = sprintf('%s=%s', $otherFilterCode, $otherFilterSelectedValue);

        $this->assertSame($expectedQueryString, $result);
    }
}
