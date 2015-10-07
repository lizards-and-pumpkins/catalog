<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

class ConfigurableProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsTheProductBuilderInterface()
    {
        $this->assertInstanceOf(ProductBuilder::class, new ConfigurableProductBuilder());
    }
}
