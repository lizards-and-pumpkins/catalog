<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetField
 */
class FacetFieldTest extends TestCase
{
    public function testFacetFieldAttributeCodeIsReturned()
    {
        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $facetField = new FacetField($stubAttributeCode);

        $this->assertSame($stubAttributeCode, $facetField->getAttributeCode());
    }

    public function testFacetFieldValuesAreReturned()
    {
        /** @var AttributeCode|\PHPUnit_Framework_MockObject_MockObject $stubAttributeCode */
        $stubAttributeCode = $this->createMock(AttributeCode::class);

        $stubFacetValueA = $this->createMock(FacetFieldValue::class);
        $stubFacetValueB = $this->createMock(FacetFieldValue::class);

        $facetField = new FacetField($stubAttributeCode, $stubFacetValueA, $stubFacetValueB);

        $result = $facetField->getValues();
        $expectedValuesArray = [$stubFacetValueA, $stubFacetValueB];

        $this->assertSame($expectedValuesArray, $result);
    }
}
