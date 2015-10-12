<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetField
 */
class SearchEngineFacetFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testFacetFieldAttributeCodeIsReturned()
    {
        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $facetFiled = new SearchEngineFacetField($stubAttributeCode);

        $this->assertSame($stubAttributeCode, $facetFiled->getAttributeCode());
    }

    public function testFacetFieldValuesAreReturned()
    {
        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);

        $stubFacetValueA = $this->getMock(SearchEngineFacetFieldValue::class, [], [], '', false);
        $stubFacetValueB = $this->getMock(SearchEngineFacetFieldValue::class, [], [], '', false);

        $facetField = new SearchEngineFacetField($stubAttributeCode, $stubFacetValueA, $stubFacetValueB);

        $result = $facetField->getValues();
        $expectedValuesArray = [$stubFacetValueA, $stubFacetValueB];

        $this->assertSame($expectedValuesArray, $result);
    }
}
