<?php


namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToVariationAttributeList;
use LizardsAndPumpkins\Import\XPathParser;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToVariationAttributeList
 * @uses   \LizardsAndPumpkins\Import\XPathParser
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList
 */
class ConfigurableProductXmlToVariationAttributeListTest extends \PHPUnit_Framework_TestCase
{
    private $testXml = '
<product>
    <variations>
        <attribute>color</attribute>
        <attribute>size</attribute>
    </variations>
</product>';

    public function testItReturnsAVariationAttributeListFromGivenXPathParser()
    {
        $converter = new ConfigurableProductXmlToVariationAttributeList();
        $parser = new XPathParser($this->testXml);
        
        $result = $converter->createVariationAttributeList($parser);
        
        $this->assertInstanceOf(ProductVariationAttributeList::class, $result);
        $this->assertCount(2, $result);
    }
}
