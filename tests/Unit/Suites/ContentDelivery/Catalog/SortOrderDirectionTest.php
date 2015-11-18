<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidSortOrderDirectionException;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection
 */
class SortOrderDirectionTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownIfInvalidSelectedSortingDirectionsIsSpecified()
    {
        $invalidSortOrderDirection = 'foo';
        $this->setExpectedException(InvalidSortOrderDirectionException::class);
        SortOrderDirection::create($invalidSortOrderDirection);
    }

    public function testSortOrderDirectionIsReturned()
    {
        $direction = 'asc';
        $result = SortOrderDirection::create($direction);
        $this->assertSame($direction, $result->getDirection());
    }
}
