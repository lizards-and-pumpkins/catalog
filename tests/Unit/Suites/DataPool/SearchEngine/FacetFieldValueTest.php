<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldValue
 */
class FacetFieldValueTest extends \PHPUnit_Framework_TestCase
{
    private $testFieldValue = 'foo';

    private $testFieldCount = 2;

    /**
     * @var FacetFieldValue
     */
    private $facetFieldValue;

    protected function setUp()
    {
        $this->facetFieldValue = new FacetFieldValue($this->testFieldValue, $this->testFieldCount);
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->facetFieldValue);
    }

    public function testArrayRepresentationOfFacetFieldValueCountIsReturned()
    {
        $expectedArray = [
            'value' => $this->testFieldValue,
            'count' => $this->testFieldCount
        ];
        $this->assertSame($expectedArray, $this->facetFieldValue->jsonSerialize());
    }
}
