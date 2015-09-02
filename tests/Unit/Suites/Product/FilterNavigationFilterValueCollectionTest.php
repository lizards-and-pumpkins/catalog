<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\FilterNavigationFilterValueCollection
 */
class FilterNavigationFilterValueCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterNavigationFilterValueCollection
     */
    private $filterValueCollection;

    /**
     * @return FilterNavigationFilterValue|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubFilterValue()
    {
        return $this->getMock(FilterNavigationFilterValue::class, [], [], '', false);
    }

    protected function setUp()
    {
        $this->filterValueCollection = new FilterNavigationFilterValueCollection;
    }

    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, $this->filterValueCollection);
    }

    public function testCollectionIsInitiallyEmpty()
    {
        $this->assertCount(0, $this->filterValueCollection);
    }

    public function testFilterValueCanBeAddedToCollection()
    {
        $stubFilterValue = $this->createStubFilterValue();
        $this->filterValueCollection->add($stubFilterValue);

        $this->assertCount(1, $this->filterValueCollection);
    }

    public function testFilterValuesArrayIsReturned()
    {
        $stubFilterValueA = $this->createStubFilterValue();
        $stubFilterValueB = $this->createStubFilterValue();

        $this->filterValueCollection->add($stubFilterValueA);
        $this->filterValueCollection->add($stubFilterValueB);

        $result = $this->filterValueCollection->getFilterValues();

        $this->assertCount(2, $result);
        $this->assertContains($stubFilterValueA, $result);
        $this->assertContains($stubFilterValueB, $result);
    }
}
