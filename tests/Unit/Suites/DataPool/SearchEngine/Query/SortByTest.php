<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\Query;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 */
class SortByTest extends TestCase
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

    /**
     * @var SortBy
     */
    private $sortBy;

    protected function setUp()
    {
        $this->stubAttributeCode = $this->createMock(AttributeCode::class);

        $this->stubSortDirection = $this->createMock(SortDirection::class);
        $this->stubSortDirection->method('__toString')->willReturn($this->testDirection);

        $this->sortBy = new SortBy($this->stubAttributeCode, $this->stubSortDirection);
    }

    public function testCanBeCreated()
    {
        $this->assertSame($this->stubAttributeCode, $this->sortBy->getAttributeCode());
        $this->assertSame($this->stubSortDirection, $this->sortBy->getSelectedDirection());
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->sortBy);
    }

    public function testArrayRepresentationOfSortByIsReturned()
    {
        $attributeCode = 'foo';

        $this->stubAttributeCode->method('__toString')->willReturn($attributeCode);

        $expectedArray = [
            'code' => $attributeCode,
            'selectedDirection' => $this->testDirection,
        ];

        $this->assertEquals($expectedArray, $this->sortBy->jsonSerialize());
    }
}
