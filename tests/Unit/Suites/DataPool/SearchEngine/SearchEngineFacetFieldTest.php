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
        $facetField = new SearchEngineFacetField($stubAttributeCode);

        $this->assertSame($stubAttributeCode, $facetField->getAttributeCode());
    }

    public function testFacetFieldValuesAreReturned()
    {
        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);

        $stubFacetValueA = $this->getMock(SearchEngineFacetFieldValueCount::class, [], [], '', false);
        $stubFacetValueB = $this->getMock(SearchEngineFacetFieldValueCount::class, [], [], '', false);

        $facetField = new SearchEngineFacetField($stubAttributeCode, $stubFacetValueA, $stubFacetValueB);

        $result = $facetField->getValues();
        $expectedValuesArray = [$stubFacetValueA, $stubFacetValueB];

        $this->assertSame($expectedValuesArray, $result);
    }
}
