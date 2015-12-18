<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Product\AttributeCode;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequest
 */
class FacetFiltersToIncludeInResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacetFilterRequestField|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFilterRequestField;

    /**
     * @var FacetFilterRequest
     */
    private $facetFilterRequest;

    protected function setUp()
    {
        $this->stubFacetFilterRequestField = $this->getMock(FacetFilterRequestField::class);
        $this->facetFilterRequest = new FacetFilterRequest($this->stubFacetFilterRequestField);
    }

    public function testFacetFilterRequestFieldsAreReturned()
    {
        $this->assertSame([$this->stubFacetFilterRequestField], $this->facetFilterRequest->getFields());
    }

    public function testAttributeCodeStringsAreReturned()
    {
        $testAttributeCodeString = 'foo';

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCode->method('__toString')->willReturn($testAttributeCodeString);

        $this->stubFacetFilterRequestField->method('getAttributeCode')->willReturn($stubAttributeCode);

        $this->assertSame([$testAttributeCodeString], $this->facetFilterRequest->getAttributeCodeStrings());
    }

    public function testAttributeCodeStringsAreMemoized()
    {
        $this->stubFacetFilterRequestField->expects($this->once())->method('getAttributeCode');
        $this->facetFilterRequest->getAttributeCodeStrings();
        $this->facetFilterRequest->getAttributeCodeStrings();
    }
}
