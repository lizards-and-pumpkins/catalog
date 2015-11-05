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

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $sortOrderConfig = SortOrderConfig::create($this->stubAttributeCode, $this->testSelectedDirection);
        $this->assertInstanceOf(\JsonSerializable::class, $sortOrderConfig);
    }

    public function testArrayRepresentationOfSortOrderConfigIsReturned()
    {
        $attributeCode = 'foo';

        $this->stubAttributeCode->method('__toString')->willReturn($attributeCode);

        $sortOrderConfig = SortOrderConfig::create($this->stubAttributeCode, $this->testSelectedDirection);
        $expectedArray = [
            'code' => $attributeCode,
            'selectedDirection' => $this->testSelectedDirection,
            'selected' => false
        ];

        $this->assertEquals($expectedArray, $sortOrderConfig->jsonSerialize());
    }
}
