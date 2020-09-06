<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\Exception\InvalidTransformationInputException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;
use LizardsAndPumpkins\Import\Price\Price;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Money\Currency;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\CurrencyPriceRangeTransformation
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange
 * @uses   \LizardsAndPumpkins\Import\Price\Price
 */
class CurrencyPriceRangeTransformationTest extends TestCase
{
    /**
     * @var CurrencyPriceRangeTransformation
     */
    private $transformation;

    /**
     * @var Currency
     */
    private $testCurrency;

    final protected function setUp(): void
    {
        $this->testCurrency = new Currency('EUR');
        $localeFactory = function () {
            return 'fr_FR';
        };
        $this->transformation = new CurrencyPriceRangeTransformation($this->testCurrency, $localeFactory);
    }

    public function testFacetFieldTransformationInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(FacetFieldTransformation::class, $this->transformation);
    }

    /**
     * @dataProvider rangeDataProvider
     */
    public function testEncodedPriceRangeIsReturned(int $rangeFrom, int $rangeTo, string $expectation): void
    {
        $stubFacetFilterRange = $this->createMock(FacetFilterRange::class);
        $stubFacetFilterRange->method('from')->willReturn($rangeFrom);
        $stubFacetFilterRange->method('to')->willReturn($rangeTo);

        $this->assertSame($expectation, $this->transformation->encode($stubFacetFilterRange));
    }

    /**
     * @return array[]
     */
    public function rangeDataProvider() : array
    {
        $convert = function ($price) {
            return Price::fromDecimalValue($price)->getAmount();
        };
        return [
            [$convert('0.01'), $convert('0.02'), '0,01 € - 0,02 €'],
            [$convert('0.01'), $convert('0.20'), '0,01 € - 0,20 €'],
            [$convert('10'), $convert('19.00'), '10,00 € - 19,00 €'],
        ];
    }

    public function testPriceRangeCanBeEncodedFromStringValues(): void
    {
        $stubFacetFilterRange = $this->createMock(FacetFilterRange::class);
        $stubFacetFilterRange->method('from')->willReturn('100000');
        $stubFacetFilterRange->method('to')->willReturn('190000');

        $this->assertSame('10,00 € - 19,00 €', $this->transformation->encode($stubFacetFilterRange));
    }

    /**
     * @dataProvider nonMatchingEncodedInputDataProvider
     */
    public function testExceptionIsThrownIfInputCanNotBeDecoded(string $nonMatchingEncodedInput): void
    {
        $this->expectException(InvalidTransformationInputException::class);
        $this->transformation->decode($nonMatchingEncodedInput);
    }

    /**
     * @return array[]
     */
    public function nonMatchingEncodedInputDataProvider() : array
    {
        return [
            ['foo'],
            ['a - b'],
            ['1.5 - 2 €'],
        ];
    }

    /**
     * @dataProvider matchingEncodedInputDataProvider
     */
    public function testFilterPricePriceRangeIsReturned(string $input, int $rangeFrom, int $rangeTo): void
    {
        $result = $this->transformation->decode($input);

        $this->assertInstanceOf(FacetFilterRange::class, $result);
        $this->assertEquals($rangeFrom, $result->from());
        $this->assertEquals($rangeTo, $result->to());
    }

    /**
     * @return array[]
     */
    public function matchingEncodedInputDataProvider() : array
    {
        $convert = function ($price) {
            return Price::fromDecimalValue($price)->getAmount();
        };
        return [
            ['0.01-0.02', $convert('0.01'), $convert('0.02')],
            ['0.01-0.20', $convert('0.01'), $convert('0.20')],
            ['10.00-19.99', $convert('10'), $convert('19.99')],
        ];
    }
}
