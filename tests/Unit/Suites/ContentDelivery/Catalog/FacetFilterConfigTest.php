<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\FacetFilterConfig
 */
class FacetFilterConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testAttributeCodeIsReturned()
    {
        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);

        $facetFilterConfig = new FacetFilterConfig($stubAttributeCode);

        $this->assertSame($stubAttributeCode, $facetFilterConfig->getAttributeCode());
    }

    public function testFacetFieldRangeCollectionIsReturned()
    {
        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);

        /** @var FacetFieldRangeCollection|\PHPUnit_Framework_MockObject_MockObject $stubFacetFieldRangeCollection */
        $stubFacetFieldRangeCollection = $this->getMock(FacetFieldRangeCollection::class, [], [], '', false);

        $facetFilterConfig = FacetFilterConfig::createRanged($stubAttributeCode, $stubFacetFieldRangeCollection);
        $this->assertSame($stubFacetFieldRangeCollection, $facetFilterConfig->getRangeCollection());
    }
}
