<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestRangedField
 */
class FacetFilterRequestRangedFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubAttributeCode;

    /**
     * @var FacetFilterRange|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFilterRange;

    /**
     * @var FacetFilterRequestRangedField
     */
    private $field;

    protected function setUp()
    {
        $this->stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $this->stubFacetFilterRange = $this->getMock(FacetFilterRange::class, [], [], '', false);
        $this->field = new FacetFilterRequestRangedField($this->stubAttributeCode);
    }

    public function testFacetFilterRequestFieldInterfaceIsImplemented()
    {
        $this->assertInstanceOf(FacetFilterRequestField::class, $this->field);
    }

    public function testFieldIsRanged()
    {
        $this->assertTrue($this->field->isRanged());
    }

    public function testAttributeCodeIsReturned()
    {
        $this->assertSame($this->stubAttributeCode, $this->field->getAttributeCode());
    }

    public function testArrayOfFacetFilterRangesIsReturned()
    {
        $this->assertContainsOnly(FacetFilterRange::class, $this->field->getRanges());
    }
}
