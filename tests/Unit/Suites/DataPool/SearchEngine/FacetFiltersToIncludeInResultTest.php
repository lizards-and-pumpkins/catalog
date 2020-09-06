<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 */
class FacetFiltersToIncludeInResultTest extends TestCase
{
    /**
     * @var FacetFilterRequestField|MockObject
     */
    private $stubFacetFilterRequestField;

    /**
     * @var FacetFiltersToIncludeInResult
     */
    private $facetFilterRequest;

    final protected function setUp(): void
    {
        $this->stubFacetFilterRequestField = $this->createMock(FacetFilterRequestField::class);
        $this->facetFilterRequest = new FacetFiltersToIncludeInResult($this->stubFacetFilterRequestField);
    }

    public function testFacetFilterRequestFieldsAreReturned(): void
    {
        $this->assertSame([$this->stubFacetFilterRequestField], $this->facetFilterRequest->getFields());
    }

    public function testAttributeCodeStringsAreReturned(): void
    {
        $testAttributeCodeString = 'foo';

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('__toString')->willReturn($testAttributeCodeString);

        $this->stubFacetFilterRequestField->method('getAttributeCode')->willReturn($stubAttributeCode);

        $this->assertSame([$testAttributeCodeString], $this->facetFilterRequest->getAttributeCodeStrings());
    }

    public function testAttributeCodeStringsAreMemoized(): void
    {
        $this->stubFacetFilterRequestField->expects($this->once())->method('getAttributeCode');
        $this->facetFilterRequest->getAttributeCodeStrings();
        $this->facetFilterRequest->getAttributeCodeStrings();
    }
}
