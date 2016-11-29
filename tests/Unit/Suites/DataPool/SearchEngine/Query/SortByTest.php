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
    private $testDirection = SortDirection::ASC;

    /**
     * @var SortDirection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSortDirection;

    protected function setUp()
    {
        $this->stubAttributeCode = $this->createMock(AttributeCode::class);
        $this->stubSortDirection = $this->createMock(SortDirection::class);
        $this->stubSortDirection->method('__toString')->willReturn($this->testDirection);
    }

    public function testUnselectedSortByCanBeCreated()
    {
        $sortBy = SortBy::createUnselected($this->stubAttributeCode, $this->stubSortDirection);

        $this->assertSame($this->stubAttributeCode, $sortBy->getAttributeCode());
        $this->assertSame($this->stubSortDirection, $sortBy->getSelectedDirection());
        $this->assertFalse($sortBy->isSelected());
    }

    public function testSelectedSortByCanBeCreated()
    {
        $sortBy = SortBy::createSelected($this->stubAttributeCode, $this->stubSortDirection);

        $this->assertSame($this->stubAttributeCode, $sortBy->getAttributeCode());
        $this->assertSame($this->stubSortDirection, $sortBy->getSelectedDirection());
        $this->assertTrue($sortBy->isSelected());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $sortBy = SortBy::createUnselected($this->stubAttributeCode, $this->stubSortDirection);
        $this->assertInstanceOf(\JsonSerializable::class, $sortBy);
    }

    public function testArrayRepresentationOfSortByIsReturned()
    {
        $attributeCode = 'foo';

        $this->stubAttributeCode->method('__toString')->willReturn($attributeCode);

        $sortBy = SortBy::createUnselected($this->stubAttributeCode, $this->stubSortDirection);
        $expectedArray = [
            'code' => $attributeCode,
            'selectedDirection' => $this->testDirection,
            'selected' => false
        ];

        $this->assertEquals($expectedArray, $sortBy->jsonSerialize());
    }
}
