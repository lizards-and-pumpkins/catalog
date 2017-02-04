<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection
 */
class FacetFieldCollectionTest extends TestCase
{
    /**
     * @param string $attributeCode
     * @param \PHPUnit_Framework_MockObject_MockObject[] $stubFacetFieldValueCount
     * @return FacetField|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubFacetField(string $attributeCode, array $stubFacetFieldValueCount) : FacetField
    {
        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('__toString')->willReturn($attributeCode);

        $stubFacetField = $this->createMock(FacetField::class);
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
        $stubFacetField = $this->createMock(FacetField::class);
        $facetFieldCollection = new FacetFieldCollection($stubFacetField);

        $this->assertCount(1, $facetFieldCollection);
    }

    public function testCollectionCanBeRetrievedViaGetter()
    {
        $stubFacetField = $this->createMock(FacetField::class);
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
        $stubFacetField = $this->createMock(FacetField::class);
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
        $stubFacetFieldAValueCount = $this->createMock(FacetFieldValue::class);
        $stubFacetFieldA = $this->createStubFacetField($attributeCodeA, [$stubFacetFieldAValueCount]);

        $attributeCodeB = 'bar';
        $stubFacetFieldBValueCount = $this->createMock(FacetFieldValue::class);
        $stubFacetFieldB = $this->createStubFacetField($attributeCodeB, [$stubFacetFieldBValueCount]);

        $facetFieldCollection = new FacetFieldCollection($stubFacetFieldA, $stubFacetFieldB);

        $expectedArray = [
            $attributeCodeA => [$stubFacetFieldAValueCount],
            $attributeCodeB => [$stubFacetFieldBValueCount]
        ];

        $this->assertSame($expectedArray, $facetFieldCollection->jsonSerialize());
    }
}
