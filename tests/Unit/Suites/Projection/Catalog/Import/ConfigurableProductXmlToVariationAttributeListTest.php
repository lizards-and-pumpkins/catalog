<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Utils\XPathParser;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToVariationAttributeList
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList
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
