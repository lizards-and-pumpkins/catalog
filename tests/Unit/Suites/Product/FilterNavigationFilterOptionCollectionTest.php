<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\FilterNavigationFilterOptionCollection
 */
class FilterNavigationFilterOptionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterNavigationFilterOptionCollection
     */
    private $filterOptionCollection;

    /**
     * @return FilterNavigationFilterOption|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubFilterOption()
    {
        return $this->getMock(FilterNavigationFilterOption::class, [], [], '', false);
    }

    protected function setUp()
    {
        $this->filterOptionCollection = new FilterNavigationFilterOptionCollection;
    }

    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, $this->filterOptionCollection);
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->filterOptionCollection);
    }

    public function testCollectionIsInitiallyEmpty()
    {
        $this->assertCount(0, $this->filterOptionCollection);
    }

    public function testCollectionIsAccessibleViaGetter()
    {
        $stubFilterOptionA = $this->createStubFilterOption();
        $stubFilterOptionB = $this->createStubFilterOption();

        $this->filterOptionCollection->add($stubFilterOptionA);
        $this->filterOptionCollection->add($stubFilterOptionB);

        $result = $this->filterOptionCollection->getFilterOptions();

        $this->assertCount(2, $result);
        $this->assertContains($stubFilterOptionA, $result);
        $this->assertContains($stubFilterOptionB, $result);
    }

    public function testCollectionIsAccessibleViaIterator()
    {
        $stubFilterOptionA = $this->createStubFilterOption();
        $stubFilterOptionB = $this->createStubFilterOption();

        $this->filterOptionCollection->add($stubFilterOptionA);
        $this->filterOptionCollection->add($stubFilterOptionB);

        $this->assertCount(2, $this->filterOptionCollection);
        $this->assertContains($stubFilterOptionA, $this->filterOptionCollection);
        $this->assertContains($stubFilterOptionB, $this->filterOptionCollection);
    }
}
