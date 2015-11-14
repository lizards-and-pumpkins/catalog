<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationCollection
 */
class FacetFieldTransformationCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $collection = new FacetFieldTransformationCollection;
        $this->assertInstanceOf(\IteratorAggregate::class, $collection);
    }

    public function testFacetFieldTransformationsAreAccessibleViaArrayIterator()
    {
        $stubFacetFieldTransformation = $this->getMock(FacetFieldTransformation::class);
        $collection = new FacetFieldTransformationCollection($stubFacetFieldTransformation);

        $result = $collection->getIterator();

        $this->assertCount(1, $result);
        $this->assertSame($stubFacetFieldTransformation, $result->current());
    }

    public function testFacetFieldTransformationsAreAccessibleViaGetter()
    {
        $stubFacetFieldTransformation = $this->getMock(FacetFieldTransformation::class);
        $collection = new FacetFieldTransformationCollection($stubFacetFieldTransformation);

        $this->assertSame([$stubFacetFieldTransformation], $collection->getTransformations());
    }
}
