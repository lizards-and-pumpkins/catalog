<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidFacetFieldValueCountException;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidFacetFieldValueException;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetFieldValue
 */
class SearchEngineFacetFieldValueTest extends \PHPUnit_Framework_TestCase
{
    private $testFieldValue = 'foo';

    private $testFieldCount = 2;

    /**
     * @var SearchEngineFacetFieldValue
     */
    private $facetFieldValue;

    protected function setUp()
    {
        $this->facetFieldValue = SearchEngineFacetFieldValue::create($this->testFieldValue, $this->testFieldCount);
    }

    public function testExceptionIsThrownIfFacetFieldValueIsNotAString()
    {
        $this->setExpectedException(InvalidFacetFieldValueException::class);

        $invalidValue = new \stdClass;
        SearchEngineFacetFieldValue::create($invalidValue, $this->testFieldCount);
    }

    public function testExceptionIsThrownIfFacetFieldValueCountIsNotInteger()
    {
        $this->setExpectedException(InvalidFacetFieldValueCountException::class);

        $invalidValueCount = [];
        SearchEngineFacetFieldValue::create($this->testFieldValue, $invalidValueCount);
    }

    public function testFacetFieldValueIsReturned()
    {
        $this->assertSame($this->testFieldValue, $this->facetFieldValue->getValue());
    }

    public function testFacetFieldValueCountIsReturned()
    {
        $this->assertSame($this->testFieldCount, $this->facetFieldValue->getCount());
    }
}
