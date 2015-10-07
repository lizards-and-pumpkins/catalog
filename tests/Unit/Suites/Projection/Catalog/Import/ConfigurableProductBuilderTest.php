<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

class ConfigurableProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsTheProductBuilderInterface()
    {
        $this->markTestIncomplete('Incomplete until ProductXmlToProductBuilder is more complete');
        $this->assertInstanceOf(ProductBuilder::class, new ConfigurableProductBuilder());
    }
}
