<?php

namespace Brera\Product;

use Brera\Http\HttpRequest;

/**
 * @covers \Brera\Product\FilterNavigationFilterCollection
 * @uses   \Brera\Product\FilterNavigationFilter
 */
class FilterNavigationFilterCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string[]
     */
    private $attributeCodesAllowedInFilterNavigation = ['foo', 'bar'];

    /**
     * @var FilterNavigationFilterCollection
     */
    private $filterCollection;

    protected function setUp()
    {
        $this->filterCollection = new FilterNavigationFilterCollection($this->attributeCodesAllowedInFilterNavigation);
    }

    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, $this->filterCollection);
    }

    public function testCollectionIsInitiallyEmpty()
    {
        $this->assertCount(0, $this->filterCollection);
    }

    public function testCollectionIsFilledFromRequest()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getQueryParameter')->willReturnMap([['foo', 'baz'], ['bar', 'qux']]);

        $this->filterCollection->fillFromRequest($stubHttpRequest);

        $this->assertCount(2, $this->filterCollection);

        $filterA = $this->filterCollection->getFilters()['foo'];
        $this->assertInstanceOf(FilterNavigationFilter::class, $filterA);
        $this->assertEquals('foo', $filterA->getCode());
        $this->assertEquals(['baz'], $filterA->getSelectedFilters());

        $filterB = $this->filterCollection->getFilters()['bar'];
        $this->assertInstanceOf(FilterNavigationFilter::class, $filterB);
        $this->assertEquals('bar', $filterB->getCode());
        $this->assertEquals(['qux'], $filterB->getSelectedFilters());
    }
}
