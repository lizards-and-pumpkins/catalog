<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetField
 */
class FacetFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testFacetFieldAttributeCodeIsReturned()
    {
        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $facetField = new FacetField($stubAttributeCode);

        $this->assertSame($stubAttributeCode, $facetField->getAttributeCode());
    }

    public function testFacetFieldValuesAreReturned()
    {
        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);

        $stubFacetValueA = $this->getMock(FacetFieldValue::class, [], [], '', false);
        $stubFacetValueB = $this->getMock(FacetFieldValue::class, [], [], '', false);

        $facetField = new FacetField($stubAttributeCode, $stubFacetValueA, $stubFacetValueB);

        $result = $facetField->getValues();
        $expectedValuesArray = [$stubFacetValueA, $stubFacetValueB];

        $this->assertSame($expectedValuesArray, $result);
    }
}
