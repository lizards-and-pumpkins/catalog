<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

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
