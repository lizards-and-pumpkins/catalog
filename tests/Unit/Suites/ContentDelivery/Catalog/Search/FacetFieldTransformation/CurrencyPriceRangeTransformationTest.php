<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\Exception\InvalidTransformationInputException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;
use LizardsAndPumpkins\Product\Price;
use SebastianBergmann\Money\Currency;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\CurrencyPriceRangeTransformation
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange
 * @uses   \LizardsAndPumpkins\Product\Price
 */
class CurrencyPriceRangeTransformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CurrencyPriceRangeTransformation
     */
    private $transformation;

    /**
     * @var Currency
     */
    private $testCurrency;

    protected function setUp()
    {
        $this->testCurrency = new Currency('EUR');
        $this->transformation = new CurrencyPriceRangeTransformation($this->testCurrency, 'fr_FR');
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
        $conv = function ($price) {
            return Price::fromDecimalValue($price)->getAmount();
        };
        return [
            [$conv('0.01'), $conv('0.02'), '0,01 € - 0,02 €'],
            [$conv('0.01'), $conv('0.20'), '0,01 € - 0,20 €'],
            [$conv('10'), $conv('19.00'), '10,00 € - 19,00 €'],
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
        $conv = function ($price) {
            return Price::fromDecimalValue($price)->getAmount();
        };
        return [
            ['0.01-0.02', $conv('0.01'), $conv('0.02')],
            ['0.01-0.20', $conv('0.01'), $conv('0.20')],
            ['10.00-19.99', $conv('10'), $conv('19.99')],
        ];
    }
}
