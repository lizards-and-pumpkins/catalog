<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestRangedField
 */
class FacetFilterRequestRangedFieldTest extends TestCase
{
    /**
     * @var AttributeCode|MockObject
     */
    private $stubAttributeCode;

    /**
     * @var FacetFilterRange|MockObject
     */
    private $stubFacetFilterRange;

    /**
     * @var FacetFilterRequestRangedField
     */
    private $field;

    final protected function setUp(): void
    {
        $this->stubAttributeCode = $this->createMock(AttributeCode::class);
        $this->stubFacetFilterRange = $this->createMock(FacetFilterRange::class);
        $this->field = new FacetFilterRequestRangedField($this->stubAttributeCode);
    }

    public function testFacetFilterRequestFieldInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(FacetFilterRequestField::class, $this->field);
    }

    public function testFieldIsRanged(): void
    {
        $this->assertTrue($this->field->isRanged());
    }

    public function testAttributeCodeIsReturned(): void
    {
        $this->assertSame($this->stubAttributeCode, $this->field->getAttributeCode());
    }

    public function testArrayOfFacetFilterRangesIsReturned(): void
    {
        $this->assertContainsOnly(FacetFilterRange::class, $this->field->getRanges());
    }
}
