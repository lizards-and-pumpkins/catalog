<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestField
 */
class FacetFilterRequestSimpleFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeCode
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
