<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FilterNavigationPriceRangesBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange
 */
class FilterNavigationPriceRangesBuilderTest extends TestCase
{
    public function testFacetFilterRangesAreReturned()
    {
        $this->assertContainsOnly(FacetFilterRange::class, FilterNavigationPriceRangesBuilder::getPriceRanges());
    }
}
