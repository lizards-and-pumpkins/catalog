<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldValue
 */
class FacetFieldValueTest extends TestCase
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

    public function testExceptionIsThrownIfFacetFieldValueIsNotAString()
    {
        $this->expectException(\TypeError::class);

        $invalidValue = new \stdClass;
        new FacetFieldValue($invalidValue, $this->testFieldCount);
    }

    public function testExceptionIsThrownIfFacetFieldValueCountIsNotInteger()
    {
        $this->expectException(\TypeError::class);

        $invalidValueCount = [];
        new FacetFieldValue($this->testFieldValue, $invalidValueCount);
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
