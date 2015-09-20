<?php

namespace LizardsAndPumpkins\Product;

/**
 * @covers \LizardsAndPumpkins\Product\FilterNavigationFilter
 */
class FilterNavigationFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $testFilterNavigationCode = 'foo';

    /**
     * @var FilterNavigationFilter
     */
    private $filter;

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
        $this->stubFilterValueCollection->method('jsonSerialize')->willReturn([]);

        $this->filter = FilterNavigationFilter::create(
            $this->testFilterNavigationCode,
            $this->stubFilterValueCollection
        );
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->filter);
    }

    public function testExceptionIsThrownDuringAttemptToCreateFilterWithNonStringAttributeCode()
    {
        $this->setExpectedException(InvalidFilterNavigationFilterCode::class);
        $invalidFilterNavigationCode = 1;
        FilterNavigationFilter::create($invalidFilterNavigationCode, $this->stubFilterValueCollection);
    }

    public function testFilterNavigationFilterIsReturned()
    {
        $this->assertSame($this->testFilterNavigationCode, $this->filter->getCode());
        $this->assertSame($this->stubFilterValueCollection, $this->filter->getOptionCollection());
    }

    public function testFilterArrayRepresentationIsReturned()
    {
        $expectedArray = [
            'code' => $this->testFilterNavigationCode,
            'options' => []
        ];

        $this->assertSame($expectedArray, $this->filter->jsonSerialize());
    }
}
