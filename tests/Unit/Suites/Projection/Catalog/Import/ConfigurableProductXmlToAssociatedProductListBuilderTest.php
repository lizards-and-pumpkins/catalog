<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Utils\XPathParser;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToAssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\AssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 */
class ConfigurableProductXmlToAssociatedProductListBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $testXml = '
<product type="configurable" sku="config">
    <associated_products>
        <product type="simple" sku="test">
        </product>
    </associated_products>
</product>
    ';

    public function testItReturnsAnAssociatedProductListBuilderInstance()
    {
        $stubXmlToProductTypeBuilderLocator = $this->getMock(ProductXmlToProductBuilderLocator::class);
        $stubXmlToProductTypeBuilderLocator->method('createProductBuilderFromXml')
            ->willReturn($this->getMock(ProductBuilder::class));
        $converter = new ConfigurableProductXmlToAssociatedProductListBuilder($stubXmlToProductTypeBuilderLocator);

        $result = $converter->createAssociatedProductListBuilder(new XPathParser($this->testXml));
        
        $this->assertInstanceOf(AssociatedProductListBuilder::class, $result);
    }
}
