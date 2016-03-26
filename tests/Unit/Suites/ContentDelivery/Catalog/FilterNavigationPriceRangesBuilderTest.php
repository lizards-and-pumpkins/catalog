<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;
use LizardsAndPumpkins\DataPool\SearchEngine\FilterNavigationPriceRangesBuilder;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FilterNavigationPriceRangesBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange
 */
class FilterNavigationPriceRangesBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testFacetFilterRangesAreReturned()
    {
        $this->assertContainsOnly(FacetFilterRange::class, FilterNavigationPriceRangesBuilder::getPriceRanges());
    }
}
