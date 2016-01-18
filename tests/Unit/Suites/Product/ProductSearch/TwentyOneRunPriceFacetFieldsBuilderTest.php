<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

class TwentyOneRunPriceFacetFieldsBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsThePriceFacetFieldsBuilderInterface()
    {
        $this->assertInstanceOf(PriceFacetFieldsBuilder::class, new TwentyOneRunPriceFacetFieldsBuilder());
    }
}
