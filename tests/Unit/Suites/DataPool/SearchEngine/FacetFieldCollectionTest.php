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
     * @param MockObject[] $stubFacetFieldValueCount
     * @return FacetField|MockObject
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

    public function testCountableInterfaceIsImplemented(): void
    {
        $facetFieldCollection = new FacetFieldCollection;
        $this->assertInstanceOf(\Countable::class, $facetFieldCollection);
    }

    public function testCollectionCountIsReturned(): void
    {
        $stubFacetField = $this->createMock(FacetField::class);
        $facetFieldCollection = new FacetFieldCollection($stubFacetField);

        $this->assertCount(1, $facetFieldCollection);
    }

    public function testCollectionCanBeRetrievedViaGetter(): void
    {
        $stubFacetField = $this->createMock(FacetField::class);
        $facetFieldCollection = new FacetFieldCollection($stubFacetField);

        $result = $facetFieldCollection->getFacetFields();
        $expectedFacetFieldsArray = [$stubFacetField];

        $this->assertSame($expectedFacetFieldsArray, $result);
    }

    public function testIteratorAggregateInterfaceIsImplemented(): void
    {
        $facetFieldCollection = new FacetFieldCollection;
        $this->assertInstanceOf(\IteratorAggregate::class, $facetFieldCollection);
    }

    public function testCollectionCanBeRetrievedViaIterator(): void
    {
        $stubFacetField = $this->createMock(FacetField::class);
        $facetFieldCollection = new FacetFieldCollection($stubFacetField);

        $result = $facetFieldCollection->getIterator();

        $this->assertCount(1, $result);
        $this->assertSame($stubFacetField, $result->current());
    }

    public function testJsonSerializableInterfaceIsImplemented(): void
    {
        $facetFieldCollection = new FacetFieldCollection;
        $this->assertInstanceOf(\JsonSerializable::class, $facetFieldCollection);
    }

    public function testArrayRepresentationOfFacetFilterCollectionIsReturned(): void
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
