<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

use LizardsAndPumpkins\Context\Context;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\EuroPriceRangeTransformation
 */
class EuroPriceRangeTransformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EuroPriceRangeTransformation
     */
    private $transformation;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @param string $expected
     * @param string $input
     */
    private function assertIsTransformedTo($expected, $input)
    {
        $transformation = $this->transformation;
        $this->assertSame($expected, $transformation($input, $this->stubContext));
    }

    protected function setUp()
    {
        $this->transformation = new EuroPriceRangeTransformation;
        $this->stubContext = $this->getMock(Context::class);
    }

    public function testFacetFieldTransformationInterfaceIsImplemented()
    {
        $this->assertInstanceOf(FacetFieldTransformation::class, $this->transformation);
    }

    public function testTransformationIsCallable()
    {
        $this->assertTrue(is_callable($this->transformation));
    }

    /**
     * @dataProvider nonMatchingInputDataProvider
     * @param string $nonMatchingInput
     */
    public function testNonMatchingInputIsNotModified($nonMatchingInput)
    {
        $this->assertIsTransformedTo($nonMatchingInput, $nonMatchingInput);
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
     * @dataProvider priceRangeTransformationProvider
     * @param string $input
     * @param string $expectation
     */
    public function testTransformedPriceRangeIsReturned($input, $expectation)
    {
        $this->assertIsTransformedTo($expectation, $input);
    }

    /**
     * @return array[]
     */
    public function priceRangeTransformationProvider()
    {
        return [
            ['1 TO 2', '0,01 € - 0,02 €'],
            ['1 TO 20', '0,01 € - 0,20 €'],
            ['1000 TO 1999', '10,00 € - 19,99 €'],
        ];
    }
}
