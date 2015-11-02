<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidSortingDirectionsException;
use LizardsAndPumpkins\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 */
class SortOrderConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SortOrderConfig
     */
    private $sortOrderConfig;

    /**
     * @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode
     */
    private $stubAttributeCode;

    /**
     * @var string
     */
    private $testSelectedDirection = 'asc';

    protected function setUp()
    {
        $this->stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $this->sortOrderConfig = SortOrderConfig::create($this->stubAttributeCode, $this->testSelectedDirection);
    }

    public function testExceptionIsThrownIfInvalidSelectedSortingDirectionsIsSpecified()
    {
        $selectedDirection = 'foo';
        $this->setExpectedException(InvalidSortingDirectionsException::class);
        SortOrderConfig::create($this->stubAttributeCode, $selectedDirection);
    }

    public function testAttributeCodeIsReturned()
    {
        $this->assertSame($this->stubAttributeCode, $this->sortOrderConfig->getAttributeCode());
    }

    public function testSelectedDirectionIsReturned()
    {
        $this->assertSame($this->testSelectedDirection, $this->sortOrderConfig->getSelectedDirection());
    }
}
