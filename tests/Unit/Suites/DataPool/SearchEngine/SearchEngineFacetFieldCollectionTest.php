<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetFieldCollection
 */
class SearchEngineFacetFieldCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCountableInterfaceIsImplemented()
    {
        $facetFiledCollection = new SearchEngineFacetFieldCollection;
        $this->assertInstanceOf(\Countable::class, $facetFiledCollection);
    }

    public function testCollectionCountIsReturned()
    {
        $stubFacetFiled = $this->getMock(SearchEngineFacetField::class, [], [], '', false);
        $facetFiledCollection = new SearchEngineFacetFieldCollection($stubFacetFiled);

        $this->assertCount(1, $facetFiledCollection);
    }

    public function testCollectionCanBeRetrievedViaGetter()
    {
        $stubFacetFiled = $this->getMock(SearchEngineFacetField::class, [], [], '', false);
        $facetFiledCollection = new SearchEngineFacetFieldCollection($stubFacetFiled);

        $result = $facetFiledCollection->getFacetFields();
        $expectedFacetFieldsArray = [$stubFacetFiled];

        $this->assertSame($expectedFacetFieldsArray, $result);
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $facetFiledCollection = new SearchEngineFacetFieldCollection;
        $this->assertInstanceOf(\IteratorAggregate::class, $facetFiledCollection);
    }

    public function testCollectionCanBeRetrievedViaIterator()
    {
        $stubFacetFiled = $this->getMock(SearchEngineFacetField::class, [], [], '', false);
        $facetFiledCollection = new SearchEngineFacetFieldCollection($stubFacetFiled);

        $result = $facetFiledCollection->getIterator();

        $this->assertCount(1, $result);
        $this->assertSame($stubFacetFiled, $result->current());
    }
}
