<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetFieldCollection
 */
class SearchEngineFacetFieldCollectionTest extends \PHPUnit_Framework_TestCase
{
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
}
