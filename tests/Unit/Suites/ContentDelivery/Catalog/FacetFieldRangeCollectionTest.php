<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\FacetFieldRangeCollection
 */
class FacetFieldRangeCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $collection = new FacetFieldRangeCollection;
        $this->assertInstanceOf(\IteratorAggregate::class, $collection);
    }

    public function testFacetFieldRangesAreAccessibleViaArrayIterator()
    {
        $stubFacetFieldRange = $this->getMock(FacetFieldRange::class, [], [], '', false);
        $collection = new FacetFieldRangeCollection($stubFacetFieldRange);

        $result = $collection->getIterator();

        $this->assertCount(1, $result);
        $this->assertSame($stubFacetFieldRange, $result->current());
    }

    public function testFacetFieldRangesAreAccessibleViaGetter()
    {
        $stubFacetFieldRange = $this->getMock(FacetFieldRange::class, [], [], '', false);
        $collection = new FacetFieldRangeCollection($stubFacetFieldRange);

        $this->assertSame([$stubFacetFieldRange], $collection->getRanges());
    }
}
