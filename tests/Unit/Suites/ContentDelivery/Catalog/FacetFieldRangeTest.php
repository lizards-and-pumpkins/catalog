<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\FacetFieldRange
 */
class FacetFieldRangeTest extends \PHPUnit_Framework_TestCase
{
    public function testRangeBoundariesAreReturned()
    {
        $testRangeFrom = 10;
        $testRangeTo = 20;

        $range = new FacetFieldRange($testRangeFrom, $testRangeTo);

        $this->assertSame($testRangeFrom, $range->from());
        $this->assertSame($testRangeTo, $range->to());
    }
}
