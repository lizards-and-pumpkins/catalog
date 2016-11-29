<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\Query;

use LizardsAndPumpkins\Import\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 */
class SortByTest extends \PHPUnit_Framework_TestCase
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
        $this->stubAttributeCode = $this->createMock(AttributeCode::class);
        $this->stubSortOrderDirection = $this->createMock(SortOrderDirection::class);
        $this->stubSortOrderDirection->method('__toString')->willReturn($this->testDirection);
    }

    public function testUnselectedSortByCanBeCreated()
    {
        $sortBy = SortBy::createUnselected($this->stubAttributeCode, $this->stubSortOrderDirection);

        $this->assertSame($this->stubAttributeCode, $sortBy->getAttributeCode());
        $this->assertSame($this->stubSortOrderDirection, $sortBy->getSelectedDirection());
        $this->assertFalse($sortBy->isSelected());
    }

    public function testSelectedSortByCanBeCreated()
    {
        $sortBy = SortBy::createSelected($this->stubAttributeCode, $this->stubSortOrderDirection);

        $this->assertSame($this->stubAttributeCode, $sortBy->getAttributeCode());
        $this->assertSame($this->stubSortOrderDirection, $sortBy->getSelectedDirection());
        $this->assertTrue($sortBy->isSelected());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $sortBy = SortBy::createUnselected($this->stubAttributeCode, $this->stubSortOrderDirection);
        $this->assertInstanceOf(\JsonSerializable::class, $sortBy);
    }

    public function testArrayRepresentationOfSortByIsReturned()
    {
        $attributeCode = 'foo';

        $this->stubAttributeCode->method('__toString')->willReturn($attributeCode);

        $sortBy = SortBy::createUnselected($this->stubAttributeCode, $this->stubSortOrderDirection);
        $expectedArray = [
            'code' => $attributeCode,
            'selectedDirection' => $this->testDirection,
            'selected' => false
        ];

        $this->assertEquals($expectedArray, $sortBy->jsonSerialize());
    }
}
