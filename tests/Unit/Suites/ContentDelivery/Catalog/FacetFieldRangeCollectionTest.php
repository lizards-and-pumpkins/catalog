<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\FacetFieldRangeCollection
 */
class FacetFieldRangeCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacetFieldRangeCollection
     */
    private $collection;

    /**
     * @var FacetFieldRange|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFieldRange;

    protected function setUp()
    {
        $this->stubFacetFieldRange = $this->getMock(FacetFieldRange::class, [], [], '', false);
        $this->collection = new FacetFieldRangeCollection($this->stubFacetFieldRange);
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $collection = new FacetFieldRangeCollection;
        $this->assertInstanceOf(\IteratorAggregate::class, $collection);
    }

    public function testFacetFieldRangesAreAccessibleViaArrayIterator()
    {
        $result = $this->collection->getIterator();

        $this->assertCount(1, $result);
        $this->assertSame($this->stubFacetFieldRange, $result->current());
    }

    public function testFacetFieldRangesAreAccessibleViaGetter()
    {
        $this->assertSame([$this->stubFacetFieldRange], $this->collection->getRanges());
    }

    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, $this->collection);
    }

    public function testCollectionCountIsReturned()
    {
        $this->assertCount(1, $this->collection);
    }
}
