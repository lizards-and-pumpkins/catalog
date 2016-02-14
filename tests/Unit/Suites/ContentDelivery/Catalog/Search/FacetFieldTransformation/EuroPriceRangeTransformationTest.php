<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\Exception\InvalidTransformationInputException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\EuroPriceRangeTransformation
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange
 */
class EuroPriceRangeTransformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EuroPriceRangeTransformation
     */
    private $transformation;

    protected function setUp()
    {
        $this->transformation = new EuroPriceRangeTransformation;
    }

    public function testFacetFieldTransformationInterfaceIsImplemented()
    {
        $this->assertInstanceOf(FacetFieldTransformation::class, $this->transformation);
    }

    /**
     * @dataProvider rangeDataProvider
     * @param int $rangeFrom
     * @param int $rangeTo
     * @param string $expectation
     */
    public function testEncodedPriceRangeIsReturned($rangeFrom, $rangeTo, $expectation)
    {
        /** @var FacetFilterRange|\PHPUnit_Framework_MockObject_MockObject $stubFacetFilterRange */
        $stubFacetFilterRange = $this->getMock(FacetFilterRange::class, [], [], '', false);
        $stubFacetFilterRange->method('from')->willReturn($rangeFrom);
        $stubFacetFilterRange->method('to')->willReturn($rangeTo);

        $this->assertSame($expectation, $this->transformation->encode($stubFacetFilterRange));
    }

    /**
     * @return array[]
     */
    public function rangeDataProvider()
    {
        return [
            [1, 2, '0,01 € - 0,02 €'],
            [1, 20, '0,01 € - 0,20 €'],
            [1000, 1999, '10,00 € - 19,99 €'],
        ];
    }

    /**
     * @dataProvider nonMatchingEncodedInputDataProvider
     * @param string $nonMatchingEncodedInput
     */
    public function testExceptionIsThrownIfInputCanNotBeDecoded($nonMatchingEncodedInput)
    {
        $this->expectException(InvalidTransformationInputException::class);
        $this->transformation->decode($nonMatchingEncodedInput);
    }

    /**
     * @return array[]
     */
    public function nonMatchingEncodedInputDataProvider()
    {
        return [
            ['foo'],
            ['a - b'],
            ['1.5 - 2 €'],
        ];
    }

    /**
     * @dataProvider matchingEncodedInputDataProvider
     * @param string $input
     * @param int $rangeFrom
     * @param int $rangeTo
     */
    public function testFilterPricePriceRangeIsReturned($input, $rangeFrom, $rangeTo)
    {
        $result = $this->transformation->decode($input);

        $this->assertInstanceOf(FacetFilterRange::class, $result);
        $this->assertEquals($rangeFrom, $result->from());
        $this->assertEquals($rangeTo, $result->to());
    }

    /**
     * @return array[]
     */
    public function matchingEncodedInputDataProvider()
    {
        return [
            ['0.01-0.02', 1, 2],
            ['0.01-0.20', 1, 20],
            ['10.00-19.99', 1000, 1999],
        ];
    }
}
