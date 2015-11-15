<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidRangeFormatException;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\FacetFieldRangeCollection
 */
class FacetFieldRangeCollectionTest extends \PHPUnit_Framework_TestCase
{
    private $testRangeOutputFormat = '$%s - $%s';

    private $testRangeInputFormat = '%s-%s';

    /**
     * @var FacetFieldRangeCollection
     */
    private $collection;

    /**
     * @var FacetFieldRange|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFieldRange;

    protected function setUp()
    {
        $this->stubFacetFieldRange = $this->getMock(FacetFieldRange::class, [], [], '', false);
        $this->collection = FacetFieldRangeCollection::create(
            $this->testRangeOutputFormat,
            $this->testRangeInputFormat,
            $this->stubFacetFieldRange
        );
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->collection);
    }

    public function testFacetFieldRangesAreAccessibleViaArrayIterator()
    {
        $result = $this->collection->getIterator();

        $this->assertCount(1, $result);
        $this->assertSame($this->stubFacetFieldRange, $result->current());
    }

    public function testFacetFieldRangesAreAccessibleViaGetter()
    {
        $this->assertSame([$this->stubFacetFieldRange], $this->collection->getRanges());
    }

    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, $this->collection);
    }

    public function testCollectionCountIsReturned()
    {
        $this->assertCount(1, $this->collection);
    }

    public function testExceptionIsThrownIfRangeOutputFormatIsNonString()
    {
        $invalidRangeOutputFormat = [];
        $this->setExpectedException(InvalidRangeFormatException::class, 'Range format must be string, got "array".');
        FacetFieldRangeCollection::create($invalidRangeOutputFormat, $this->testRangeInputFormat);
    }

    public function testExceptionIsThrownIfRangeInputFormatIsNonString()
    {
        $invalidRangeInputFormat = [];
        $this->setExpectedException(InvalidRangeFormatException::class, 'Range format must be string, got "array".');
        FacetFieldRangeCollection::create($this->testRangeOutputFormat, $invalidRangeInputFormat);
    }

    /**
     * @dataProvider invalidRangeFormatDataProvider
     * @param string $invalidRangeOutputFormat
     */
    public function testExceptionIsThrownIfRangeOutputFormatHasInvalidNumberOfPlaceholders($invalidRangeOutputFormat)
    {
        $this->setExpectedException(InvalidRangeFormatException::class);
        FacetFieldRangeCollection::create($invalidRangeOutputFormat, $this->testRangeInputFormat);
    }

    /**
     * @dataProvider invalidRangeFormatDataProvider
     * @param string $invalidRangeInputFormat
     */
    public function testExceptionIsThrownIfRangeInputFormatHasInvalidNumberOfPlaceholders($invalidRangeInputFormat)
    {
        $this->setExpectedException(InvalidRangeFormatException::class);
        FacetFieldRangeCollection::create($invalidRangeInputFormat, $this->testRangeInputFormat);
    }

    /**
     * @return array[]
     */
    public function invalidRangeFormatDataProvider()
    {
        return [
            [''],
            ['%s'],
            ['%s - %s - %s'],
        ];
    }

    public function testRangeOutputFormatIsReturned()
    {
        $this->assertSame($this->testRangeOutputFormat, $this->collection->getRangeOutputFormat());
    }

    public function testRangeInputFormatIsReturned()
    {
        $this->assertSame($this->testRangeInputFormat, $this->collection->getRangeInputFormat());
    }
}
