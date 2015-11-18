<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\EuroPriceRangeTransformation
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
     * @dataProvider nonMatchingInputDataProvider
     * @param string $nonMatchingInput
     */
    public function testNonMatchingInputIsNotEncoded($nonMatchingInput)
    {
        $result = $this->transformation->encode($nonMatchingInput);
        $this->assertSame($nonMatchingInput, $result);
    }

    public function nonMatchingInputDataProvider()
    {
        return [
            ['foo'],
            ['a TO b'],
            ['1.5 TO 2'],
        ];
    }

    /**
     * @dataProvider matchingInputDataProvider
     * @param string $input
     * @param string $expectation
     */
    public function testEncodedPriceRangeIsReturned($input, $expectation)
    {
        $result = $this->transformation->encode($input);
        $this->assertSame($expectation, $result);
    }

    /**
     * @return array[]
     */
    public function matchingInputDataProvider()
    {
        return [
            ['1 TO 2', '0,01 € - 0,02 €'],
            ['1 TO 20', '0,01 € - 0,20 €'],
            ['1000 TO 1999', '10,00 € - 19,99 €'],
        ];
    }

    /**
     * @dataProvider nonMatchingEncodedInputDataProvider
     * @param string $nonMatchingEncodedInput
     */
    public function testNonMatchingEncodedInputIsNotDecoded($nonMatchingEncodedInput)
    {
        $result = $this->transformation->decode($nonMatchingEncodedInput);
        $this->assertSame($nonMatchingEncodedInput, $result);
    }

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
     * @param string $expectation
     */
    public function testDecodedPriceRangeIsReturned($input, $expectation)
    {
        $result = $this->transformation->decode($input);
        $this->assertSame($expectation, $result);
    }

    /**
     * @return array[]
     */
    public function matchingEncodedInputDataProvider()
    {
        return [
            ['0,01 € - 0,02 €', '1 TO 2'],
            ['0,01 € - 0,20 €', '1 TO 20'],
            ['10,00 € - 19,99 €', '1000 TO 1999'],
        ];
    }
}
