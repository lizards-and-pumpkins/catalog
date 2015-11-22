<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequest
 */
class FacetFilterRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testFacetFilterRequestFieldsAreReturned()
    {
        $stubFacetFilterRequestField = $this->getMock(FacetFilterRequestField::class);
        $facetFilterRequest = new FacetFilterRequest($stubFacetFilterRequestField);
        $result = $facetFilterRequest->getFields();

        $this->assertSame([$stubFacetFilterRequestField], $result);
    }
}
