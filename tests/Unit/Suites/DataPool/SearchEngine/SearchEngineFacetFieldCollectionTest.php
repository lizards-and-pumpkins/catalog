<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetFieldCollection
 */
class SearchEngineFacetFieldCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $attributeCode
     * @param \PHPUnit_Framework_MockObject_MockObject[] $stubFacetFieldValueCount
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubFacetField($attributeCode, array $stubFacetFieldValueCount)
    {
        $stubFacetField = $this->getMock(SearchEngineFacetField::class, [], [], '', false);
        $stubFacetField->method('getAttributeCode')->willReturn($attributeCode);
        $stubFacetField->method('getValues')->willReturn($stubFacetFieldValueCount);
        return $stubFacetField;
    }

    public function testCountableInterfaceIsImplemented()
    {
        $facetFieldCollection = new SearchEngineFacetFieldCollection;
        $this->assertInstanceOf(\Countable::class, $facetFieldCollection);
    }

    public function testCollectionCountIsReturned()
    {
        $stubFacetField = $this->getMock(SearchEngineFacetField::class, [], [], '', false);
        $facetFieldCollection = new SearchEngineFacetFieldCollection($stubFacetField);

        $this->assertCount(1, $facetFieldCollection);
    }

    public function testCollectionCanBeRetrievedViaGetter()
    {
        $stubFacetField = $this->getMock(SearchEngineFacetField::class, [], [], '', false);
        $facetFieldCollection = new SearchEngineFacetFieldCollection($stubFacetField);

        $result = $facetFieldCollection->getFacetFields();
        $expectedFacetFieldsArray = [$stubFacetField];

        $this->assertSame($expectedFacetFieldsArray, $result);
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $facetFieldCollection = new SearchEngineFacetFieldCollection;
        $this->assertInstanceOf(\IteratorAggregate::class, $facetFieldCollection);
    }

    public function testCollectionCanBeRetrievedViaIterator()
    {
        $stubFacetField = $this->getMock(SearchEngineFacetField::class, [], [], '', false);
        $facetFieldCollection = new SearchEngineFacetFieldCollection($stubFacetField);

        $result = $facetFieldCollection->getIterator();

        $this->assertCount(1, $result);
        $this->assertSame($stubFacetField, $result->current());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $facetFieldCollection = new SearchEngineFacetFieldCollection;
        $this->assertInstanceOf(\JsonSerializable::class, $facetFieldCollection);
    }

    public function testArrayRepresentationOfFacetFilterCollectionIsReturned()
    {
        $attributeCodeA = 'foo';
        $stubFacetFieldAValueCount = $this->getMock(SearchEngineFacetFieldValueCount::class, [], [], '', false);
        $stubFacetFieldA = $this->createStubFacetField($attributeCodeA, [$stubFacetFieldAValueCount]);

        $attributeCodeB = 'bar';
        $stubFacetFieldBValueCount = $this->getMock(SearchEngineFacetFieldValueCount::class, [], [], '', false);
        $stubFacetFieldB = $this->createStubFacetField($attributeCodeB, [$stubFacetFieldBValueCount]);

        $facetFieldCollection = new SearchEngineFacetFieldCollection($stubFacetFieldA, $stubFacetFieldB);

        $expectedArray = [
            $attributeCodeA => [$stubFacetFieldAValueCount],
            $attributeCodeB => [$stubFacetFieldBValueCount]
        ];

        $this->assertSame($expectedArray, $facetFieldCollection->jsonSerialize());
    }
}
