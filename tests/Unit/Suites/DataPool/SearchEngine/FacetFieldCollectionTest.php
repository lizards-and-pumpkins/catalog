<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection
 */
class FacetFieldCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $attributeCode
     * @param \PHPUnit_Framework_MockObject_MockObject[] $stubFacetFieldValueCount
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubFacetField($attributeCode, array $stubFacetFieldValueCount)
    {
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCode->method('__toString')->willReturn($attributeCode);

        $stubFacetField = $this->getMock(FacetField::class, [], [], '', false);
        $stubFacetField->method('getAttributeCode')->willReturn($stubAttributeCode);
        $stubFacetField->method('getValues')->willReturn($stubFacetFieldValueCount);

        return $stubFacetField;
    }

    public function testCountableInterfaceIsImplemented()
    {
        $facetFieldCollection = new FacetFieldCollection;
        $this->assertInstanceOf(\Countable::class, $facetFieldCollection);
    }

    public function testCollectionCountIsReturned()
    {
        $stubFacetField = $this->getMock(FacetField::class, [], [], '', false);
        $facetFieldCollection = new FacetFieldCollection($stubFacetField);

        $this->assertCount(1, $facetFieldCollection);
    }

    public function testCollectionCanBeRetrievedViaGetter()
    {
        $stubFacetField = $this->getMock(FacetField::class, [], [], '', false);
        $facetFieldCollection = new FacetFieldCollection($stubFacetField);

        $result = $facetFieldCollection->getFacetFields();
        $expectedFacetFieldsArray = [$stubFacetField];

        $this->assertSame($expectedFacetFieldsArray, $result);
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $facetFieldCollection = new FacetFieldCollection;
        $this->assertInstanceOf(\IteratorAggregate::class, $facetFieldCollection);
    }

    public function testCollectionCanBeRetrievedViaIterator()
    {
        $stubFacetField = $this->getMock(FacetField::class, [], [], '', false);
        $facetFieldCollection = new FacetFieldCollection($stubFacetField);

        $result = $facetFieldCollection->getIterator();

        $this->assertCount(1, $result);
        $this->assertSame($stubFacetField, $result->current());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $facetFieldCollection = new FacetFieldCollection;
        $this->assertInstanceOf(\JsonSerializable::class, $facetFieldCollection);
    }

    public function testArrayRepresentationOfFacetFilterCollectionIsReturned()
    {
        $attributeCodeA = 'foo';
        $stubFacetFieldAValueCount = $this->getMock(FacetFieldValue::class, [], [], '', false);
        $stubFacetFieldA = $this->createStubFacetField($attributeCodeA, [$stubFacetFieldAValueCount]);

        $attributeCodeB = 'bar';
        $stubFacetFieldBValueCount = $this->getMock(FacetFieldValue::class, [], [], '', false);
        $stubFacetFieldB = $this->createStubFacetField($attributeCodeB, [$stubFacetFieldBValueCount]);

        $facetFieldCollection = new FacetFieldCollection($stubFacetFieldA, $stubFacetFieldB);

        $expectedArray = [
            $attributeCodeA => [$stubFacetFieldAValueCount],
            $attributeCodeB => [$stubFacetFieldBValueCount]
        ];

        $this->assertSame($expectedArray, $facetFieldCollection->jsonSerialize());
    }
}
