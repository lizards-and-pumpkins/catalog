<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\FilterNavigationPriceRangesBuilder
 */
class FilterNavigationPriceRangesBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testPriceRangeIsReturned()
    {
        array_map(function (array $rangeStep) {
            $this->assertArrayHasKey('from', $rangeStep);
            $this->assertArrayHasKey('to', $rangeStep);
        }, FilterNavigationPriceRangesBuilder::getPriceRanges());
    }
}
