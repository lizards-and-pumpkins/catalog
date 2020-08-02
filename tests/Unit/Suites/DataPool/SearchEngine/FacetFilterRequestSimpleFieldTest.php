<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 */
class FacetFilterRequestSimpleFieldTest extends TestCase
{
    /**
     * @var AttributeCode|MockObject
     */
    private $stubAttributeCode;

    /**
     * @var FacetFilterRequestSimpleField
     */
    private $field;

    final protected function setUp(): void
    {
        $this->stubAttributeCode = $this->createMock(AttributeCode::class);
        $this->field = new FacetFilterRequestSimpleField($this->stubAttributeCode);
    }

    public function testFacetFilterRequestFiledInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(FacetFilterRequestField::class, $this->field);
    }

    public function testFieldIsNotRanged(): void
    {
        $this->assertFalse($this->field->isRanged());
    }

    public function testAttributeCodeIsReturned(): void
    {
        $this->assertSame($this->stubAttributeCode, $this->field->getAttributeCode());
    }
}
