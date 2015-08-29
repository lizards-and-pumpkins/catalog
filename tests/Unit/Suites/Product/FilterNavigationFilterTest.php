<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\FilterNavigationFilter
 */
class FilterNavigationFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateFilterWithNonStringAttributeCode()
    {
        $this->setExpectedException(InvalidFilterNavigationFilterCode::class);

        $invalidFilterNavigationCode = 1;
        $selectedFilters = [];

        FilterNavigationFilter::create($invalidFilterNavigationCode, $selectedFilters);
    }

    public function testFilterNavigationFilterCodeIsReturned()
    {
        $filterNavigationCode = 'foo';
        $selectedFilters = [];

        $filter = FilterNavigationFilter::create($filterNavigationCode, $selectedFilters);
        $result = $filter->getCode();

        $this->assertSame($filterNavigationCode, $result);
    }

    public function testSelectedFiltersAreReturned()
    {
        $filterNavigationCode = 'foo';
        $selectedFilters = ['bar', 'baz'];

        $filter = FilterNavigationFilter::create($filterNavigationCode, $selectedFilters);
        $result = $filter->getSelectedFilters();

        $this->assertSame($selectedFilters, $result);
    }
}
