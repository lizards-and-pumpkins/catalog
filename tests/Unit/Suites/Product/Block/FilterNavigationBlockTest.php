<?php

namespace Brera\Product\Block;

use Brera\Product\FilterNavigationFilterCollection;
use Brera\Product\FilterNavigationFilterOption;
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

    /**
     * @param string $filterCode
     * @param string $filterOptionValue
     * @return FilterNavigationFilterOption|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubFilterOption($filterCode, $filterOptionValue)
    {
        $stubFilterOptionValue = $this->getMock(FilterNavigationFilterOption::class, [], [], '', false);
        $stubFilterOptionValue->method('getCode')->willReturn($filterCode);
        $stubFilterOptionValue->method('getValue')->willReturn($filterOptionValue);

        return $stubFilterOptionValue;
    }

    protected function setUp()
    {
        $this->stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $blockName = 'foo';
        $this->stubFilterCollection = $this->getMock(FilterNavigationFilterCollection::class, [], [], '', false);
        $stubDataObject = $this->stubFilterCollection;

        $this->block = new FilterNavigationBlock($this->stubBlockRenderer, 'foo.phtml', $blockName, $stubDataObject);
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
        $filterOptionValue = 'bar';
        $filterOption = $this->createStubFilterOption($filterCode, $filterOptionValue);

        $this->stubFilterCollection->method('getSelectedFilters')->willReturn([$filterCode => []]);

        $result = $this->block->getQueryStringForFilterSelection($filterOption);
        $expectedQueryString = sprintf('%s=%s', $filterCode, $filterOptionValue);

        $this->assertEquals($expectedQueryString, $result);
    }
    
    public function testQueryStringContainsAllSelectedFiltersPlusGivenFilter()
    {
        $newFilterCode = 'foo';
        $newFilterOptionValue = 'bar';
        $filterOption = $this->createStubFilterOption($newFilterCode, $newFilterOptionValue);

        $existingFilterCode = 'baz';
        $existingFilterSelectedOptionValue = 'qux';

        $this->stubFilterCollection->method('getSelectedFilters')
            ->willReturn([$existingFilterCode => [$existingFilterSelectedOptionValue], $newFilterCode => []]);

        $result = $this->block->getQueryStringForFilterSelection($filterOption);
        $resultTokensArray = explode('&', $result);

        $expectedOldToken = sprintf('%s=%s', $existingFilterCode, $existingFilterSelectedOptionValue);
        $expectedNewToken = sprintf('%s=%s', $newFilterCode, $newFilterOptionValue);

        $this->assertCount(2, $resultTokensArray);
        $this->assertContains($expectedOldToken, $resultTokensArray);
        $this->assertContains($expectedNewToken, $resultTokensArray);
    }

    public function testSelectedValueShouldBeAddedToPreviouslySelectedValuesOfAFilter()
    {
        $filterCode = 'foo';
        $filterOptionValue = 'bar';
        $filterOption = $this->createStubFilterOption($filterCode, $filterOptionValue);

        $previouslySelectedFilterOptionValue = 'baz';

        $this->stubFilterCollection->method('getSelectedFilters')
            ->willReturn([$filterCode => [$previouslySelectedFilterOptionValue]]);

        $result = $this->block->getQueryStringForFilterSelection($filterOption);
        $expectedQueryString = sprintf(
            '%s=%s%s%s',
            $filterCode,
            $previouslySelectedFilterOptionValue,
            urlencode(FilterNavigationBlock::VALUES_SEPARATOR),
            $filterOptionValue
        );

        $this->assertSame($expectedQueryString, $result);
    }
    
    public function testFilterValueIsRemovedFromQueryStringIfPreviouslySelected()
    {
        $filterCode = 'foo';
        $filterOptionValue = 'bar';
        $filterOption = $this->createStubFilterOption($filterCode, $filterOptionValue);

        $filterOtherSelectedValue = 'some-other-value-which-should-not-be-removed';

        $otherFilterCode = 'baz';
        $otherFilterSelectedOptionValue = 'qux';

        $this->stubFilterCollection->method('getSelectedFilters')->willReturn([
            $otherFilterCode => [$otherFilterSelectedOptionValue],
            $filterCode      => [$filterOptionValue, $filterOtherSelectedValue]
        ]);

        $result = $this->block->getQueryStringForFilterSelection($filterOption);
        $resultTokensArray = explode('&', $result);

        $expectedFilterToken = sprintf('%s=%s', $filterCode, $filterOtherSelectedValue);
        $expectedOtherFilterToken = sprintf('%s=%s', $otherFilterCode, $otherFilterSelectedOptionValue);

        $this->assertCount(2, $resultTokensArray);
        $this->assertContains($expectedOtherFilterToken, $resultTokensArray);
        $this->assertContains($expectedFilterToken, $resultTokensArray);
    }

    public function testFilterIsRemovedFromQueryStringIfLastSelectedValueWasUnset()
    {
        $filterCode = 'foo';
        $filterOptionValue = 'bar';
        $filterOption = $this->createStubFilterOption($filterCode, $filterOptionValue);

        $otherFilterCode = 'baz';
        $otherFilterSelectedOptionValue = 'qux';

        $this->stubFilterCollection->method('getSelectedFilters')->willReturn([
            $otherFilterCode => [$otherFilterSelectedOptionValue],
            $filterCode      => [$filterOptionValue]
        ]);

        $result = $this->block->getQueryStringForFilterSelection($filterOption);
        $expectedQueryString = sprintf('%s=%s', $otherFilterCode, $otherFilterSelectedOptionValue);

        $this->assertSame($expectedQueryString, $result);
    }
}
