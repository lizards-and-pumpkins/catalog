<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\FacetFilterConfigCollection
 */
class FacetFilterConfigCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacetFilterConfigCollection
     */
    private $collection;

    /**
     * @var FacetFilterConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFilterConfig;

    protected function setUp()
    {
        $this->stubFacetFilterConfig = $this->getMock(FacetFilterConfig::class, [], [], '', false);
        $this->collection = new FacetFilterConfigCollection($this->stubFacetFilterConfig);
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->collection);
    }

    public function testFacetFilterConfigsAreAccessibleViaArrayIterator()
    {
        $result = $this->collection->getIterator();
        $this->assertCount(1, $result);
        $this->assertSame($this->stubFacetFilterConfig, $result->current());
    }

    public function testFacetFieldRangesAreAccessibleViaGetter()
    {
        $this->assertSame([$this->stubFacetFilterConfig], $this->collection->getConfigs());
    }

    public function testArrayOfAttributeCodeStringsIsReturned()
    {
        $testAttributeCode = 'foo';

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCode->method('__toString')->willReturn($testAttributeCode);

        $this->stubFacetFilterConfig->method('getAttributeCode')->willReturn($stubAttributeCode);

        $this->assertSame([$testAttributeCode], $this->collection->getAttributeCodes());
    }

    public function testFilterConfigsAreNotReiteratedOnSubsequentRetrievalsOfAttributeCodes()
    {
        $this->stubFacetFilterConfig->expects($this->once())->method('getAttributeCode');

        $this->collection->getAttributeCodes();
        $this->collection->getAttributeCodes();
    }
}
