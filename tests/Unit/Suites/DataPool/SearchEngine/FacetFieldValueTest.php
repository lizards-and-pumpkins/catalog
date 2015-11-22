<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidFacetFieldValueCountException;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidFacetFieldValueException;

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
        $this->facetFieldValue = FacetFieldValue::create($this->testFieldValue, $this->testFieldCount);
    }

    public function testExceptionIsThrownIfFacetFieldValueIsNotAString()
    {
        $this->setExpectedException(InvalidFacetFieldValueException::class);

        $invalidValue = new \stdClass;
        FacetFieldValue::create($invalidValue, $this->testFieldCount);
    }

    public function testExceptionIsThrownIfFacetFieldValueCountIsNotInteger()
    {
        $this->setExpectedException(InvalidFacetFieldValueCountException::class);

        $invalidValueCount = [];
        FacetFieldValue::create($this->testFieldValue, $invalidValueCount);
    }

    public function testFacetFieldValueIsReturned()
    {
        $this->assertSame($this->testFieldValue, $this->facetFieldValue->getValue());
    }

    public function testFacetFieldValueCountIsReturned()
    {
        $this->assertSame($this->testFieldCount, $this->facetFieldValue->getCount());
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
