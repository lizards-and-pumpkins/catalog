<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetField
 */
class SearchEngineFacetFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $value
     * @param int $count
     * @return SearchEngineFacetFieldValueCount|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubFacetFiledValue($value, $count)
    {
        /** @var SearchEngineFacetFieldValueCount|\PHPUnit_Framework_MockObject_MockObject $stubFacetValue */
        $stubFacetValue = $this->getMock(SearchEngineFacetFieldValueCount::class, [], [], '', false);
        $stubFacetValue->method('getValue')->willReturn($value);
        $stubFacetValue->method('getCount')->willReturn($count);

        return $stubFacetValue;
    }

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

    public function testJsonSerializableInterfaceIsImplemented()
    {
        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $facetField = new SearchEngineFacetField($stubAttributeCode);

        $this->assertInstanceOf(\JsonSerializable::class, $facetField);
    }

    public function testArrayRepresentationOfFacetFieldIsReturned()
    {
        $attributeCodeString = 'foo';

        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCode->method('__toString')->willReturn($attributeCodeString);

        $stubFacetValueAValue = 'bar';
        $stubFacetValueACount = 1;
        $stubFacetValueA = $this->createStubFacetFiledValue($stubFacetValueAValue, $stubFacetValueACount);

        $stubFacetValueBValue = 'baz';
        $stubFacetValueBCount = 2;
        $stubFacetValueB = $this->createStubFacetFiledValue($stubFacetValueBValue, $stubFacetValueBCount);

        $facetField = new SearchEngineFacetField($stubAttributeCode, $stubFacetValueA, $stubFacetValueB);

        $expectedArray = [
            $attributeCodeString => [
                $stubFacetValueAValue => $stubFacetValueACount,
                $stubFacetValueBValue => $stubFacetValueBCount,
            ]
        ];
        $this->assertSame($expectedArray, $facetField->jsonSerialize());
    }
}
