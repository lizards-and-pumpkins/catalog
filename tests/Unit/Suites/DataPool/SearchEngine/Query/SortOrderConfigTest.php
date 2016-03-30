<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\Query;

use LizardsAndPumpkins\Import\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig
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
    private $testDirection = SortOrderDirection::ASC;

    /**
     * @var SortOrderDirection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSortOrderDirection;

    protected function setUp()
    {
        $this->stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $this->stubSortOrderDirection = $this->getMock(SortOrderDirection::class, [], [], '', false);
        $this->stubSortOrderDirection->method('__toString')->willReturn($this->testDirection);
    }

    public function testUnselectedSortOrderConfigCanBeCreated()
    {
        $sortOrderConfig = SortOrderConfig::create($this->stubAttributeCode, $this->stubSortOrderDirection);

        $this->assertSame($this->stubAttributeCode, $sortOrderConfig->getAttributeCode());
        $this->assertSame($this->stubSortOrderDirection, $sortOrderConfig->getSelectedDirection());
        $this->assertFalse($sortOrderConfig->isSelected());
    }

    public function testSelectedSortOrderConfigCanBeCreated()
    {
        $sortOrderConfig = SortOrderConfig::createSelected($this->stubAttributeCode, $this->stubSortOrderDirection);

        $this->assertSame($this->stubAttributeCode, $sortOrderConfig->getAttributeCode());
        $this->assertSame($this->stubSortOrderDirection, $sortOrderConfig->getSelectedDirection());
        $this->assertTrue($sortOrderConfig->isSelected());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $sortOrderConfig = SortOrderConfig::create($this->stubAttributeCode, $this->stubSortOrderDirection);
        $this->assertInstanceOf(\JsonSerializable::class, $sortOrderConfig);
    }

    public function testArrayRepresentationOfSortOrderConfigIsReturned()
    {
        $attributeCode = 'foo';

        $this->stubAttributeCode->method('__toString')->willReturn($attributeCode);

        $sortOrderConfig = SortOrderConfig::create($this->stubAttributeCode, $this->stubSortOrderDirection);
        $expectedArray = [
            'code' => $attributeCode,
            'selectedDirection' => $this->testDirection,
            'selected' => false
        ];

        $this->assertEquals($expectedArray, $sortOrderConfig->jsonSerialize());
    }
}
