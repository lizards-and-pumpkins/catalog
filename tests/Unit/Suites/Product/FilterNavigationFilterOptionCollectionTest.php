<?php

namespace LizardsAndPumpkins\Product;

/**
 * @covers \LizardsAndPumpkins\Product\FilterNavigationFilterOptionCollection
 */
class FilterNavigationFilterOptionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return FilterNavigationFilterOption|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubFilterOption()
    {
        return $this->getMock(FilterNavigationFilterOption::class, [], [], '', false);
    }

    public function testCountableInterfaceIsImplemented()
    {
        $filterOptionCollection = new FilterNavigationFilterOptionCollection;
        $this->assertInstanceOf(\Countable::class, $filterOptionCollection);
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $filterOptionCollection = new FilterNavigationFilterOptionCollection;
        $this->assertInstanceOf(\IteratorAggregate::class, $filterOptionCollection);
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $filterOptionCollection = new FilterNavigationFilterOptionCollection;
        $this->assertInstanceOf(\JsonSerializable::class, $filterOptionCollection);
    }

    public function testCollectionIsAccessibleViaGetter()
    {
        $stubFilterOptionA = $this->createStubFilterOption();
        $stubFilterOptionB = $this->createStubFilterOption();
        $filterOptionCollection = new FilterNavigationFilterOptionCollection($stubFilterOptionA, $stubFilterOptionB);

        $result = $filterOptionCollection->getOptions();

        $this->assertCount(2, $result);
        $this->assertContains($stubFilterOptionA, $result);
        $this->assertContains($stubFilterOptionB, $result);
    }

    public function testCollectionIsAccessibleViaIterator()
    {
        $stubFilterOptionA = $this->createStubFilterOption();
        $stubFilterOptionB = $this->createStubFilterOption();
        $filterOptionCollection = new FilterNavigationFilterOptionCollection($stubFilterOptionA, $stubFilterOptionB);

        $this->assertCount(2, $filterOptionCollection);
        $this->assertContains($stubFilterOptionA, $filterOptionCollection);
        $this->assertContains($stubFilterOptionB, $filterOptionCollection);
    }

    public function testArrayRepresentationOfFilterOptionCollectionIsReturned()
    {
        $stubFilterOption = $this->createStubFilterOption();
        $filterOptionCollection = new FilterNavigationFilterOptionCollection($stubFilterOption);

        $result = $filterOptionCollection->jsonSerialize();

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
    }
}
