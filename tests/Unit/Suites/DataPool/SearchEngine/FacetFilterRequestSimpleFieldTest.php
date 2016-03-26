<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 */
class FacetFilterRequestSimpleFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubAttributeCode;

    /**
     * @var FacetFilterRequestSimpleField
     */
    private $field;

    protected function setUp()
    {
        $this->stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $this->field = new FacetFilterRequestSimpleField($this->stubAttributeCode);
    }

    public function testFacetFilterRequestFiledInterfaceIsImplemented()
    {
        $this->assertInstanceOf(FacetFilterRequestField::class, $this->field);
    }

    public function testFieldIsNotRanged()
    {
        $this->assertFalse($this->field->isRanged());
    }

    public function testAttributeCodeIsReturned()
    {
        $this->assertSame($this->stubAttributeCode, $this->field->getAttributeCode());
    }
}
