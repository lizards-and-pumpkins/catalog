<?php

namespace LizardsAndPumpkins\Product;

/**
 * @covers \LizardsAndPumpkins\Product\FilterNavigationFilter
 */
class FilterNavigationFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterNavigationFilterOptionCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFilterValueCollection;

    protected function setUp()
    {
        $this->stubFilterValueCollection = $this->getMock(
            FilterNavigationFilterOptionCollection::class,
            [],
            [],
            '',
            false
        );
    }

    public function testExceptionIsThrownDuringAttemptToCreateFilterWithNonStringAttributeCode()
    {
        $this->setExpectedException(InvalidFilterNavigationFilterCode::class);
        $invalidFilterNavigationCode = 1;
        FilterNavigationFilter::create($invalidFilterNavigationCode, $this->stubFilterValueCollection);
    }

    public function testFilterNavigationFilterIsReturned()
    {
        $filterNavigationCode = 'foo';
        $filter = FilterNavigationFilter::create($filterNavigationCode, $this->stubFilterValueCollection);

        $this->assertSame($filterNavigationCode, $filter->getCode());
        $this->assertSame($this->stubFilterValueCollection, $filter->getOptionCollection());
    }
}
