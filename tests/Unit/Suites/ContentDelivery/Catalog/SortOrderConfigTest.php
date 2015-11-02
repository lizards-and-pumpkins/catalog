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
    }

    public function testExceptionIsThrownIfInvalidSelectedSortingDirectionsIsSpecified()
    {
        $selectedDirection = 'foo';
        $this->setExpectedException(InvalidSortingDirectionsException::class);
        SortOrderConfig::create($this->stubAttributeCode, $selectedDirection);
    }

    public function testUnselectedSortOrderConfigCanBeCreated()
    {
        $sortOrderConfig = SortOrderConfig::create($this->stubAttributeCode, $this->testSelectedDirection);

        $this->assertSame($this->stubAttributeCode, $sortOrderConfig->getAttributeCode());
        $this->assertSame($this->testSelectedDirection, $sortOrderConfig->getSelectedDirection());
        $this->assertFalse($sortOrderConfig->isSelected());
    }

    public function testSelectedSortOrderConfigCanBeCreated()
    {
        $sortOrderConfig = SortOrderConfig::createSelected($this->stubAttributeCode, $this->testSelectedDirection);

        $this->assertSame($this->stubAttributeCode, $sortOrderConfig->getAttributeCode());
        $this->assertSame($this->testSelectedDirection, $sortOrderConfig->getSelectedDirection());
        $this->assertTrue($sortOrderConfig->isSelected());
    }
}
