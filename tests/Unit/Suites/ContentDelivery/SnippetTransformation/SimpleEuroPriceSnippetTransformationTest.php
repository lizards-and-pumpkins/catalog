<?php


namespace LizardsAndPumpkins\ContentDelivery\SnippetTransformation;

use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\SnippetTransformation;
use LizardsAndPumpkins\ProductDetail\ContentDelivery\SimpleEuroPriceSnippetTransformation;

/**
 * @covers LizardsAndPumpkins\ProductDetail\ContentDelivery\SimpleEuroPriceSnippetTransformation
 */
class SimpleEuroPriceSnippetTransformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SimpleEuroPriceSnippetTransformation
     */
    private $transformation;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var PageSnippets|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubPageSnippets;

    /**
     * @param string $expected
     * @param int|string|null $input
     */
    private function assertIsTransformedTo($expected, $input)
    {
        $transformation = $this->transformation;
        $this->assertSame($expected, $transformation($input, $this->stubContext, $this->stubPageSnippets));
    }

    protected function setUp()
    {
        $this->transformation = new SimpleEuroPriceSnippetTransformation();
        $this->stubContext = $this->getMock(Context::class);
        $this->stubPageSnippets = $this->getMock(PageSnippets::class);
    }

    public function testItIsCallable()
    {
        $this->assertInstanceOf(SnippetTransformation::class, $this->transformation);
        $this->assertTrue(is_callable($this->transformation), "Snippet transformations not callable");
    }

    public function testItIgnoresInputContainingNotOnlyNumbers()
    {
        $this->assertIsTransformedTo('12,3', '12,3');
        $this->assertIsTransformedTo('12.3', '12.3');
        $this->assertIsTransformedTo('12.30 €', '12.30 €');
    }

    public function testItReturnsNullInputAsAnEmptyString()
    {
        $this->assertIsTransformedTo('', null);
    }

    public function testItReturnsArrayInputAsAnEmptyString()
    {
        $this->assertIsTransformedTo('', []);
    }

    public function testItReturnsAnEmptyStringAsAnEmptyString()
    {
        $this->assertIsTransformedTo('', '');
    }

    /**
     * @dataProvider numbersOnlyInputDataProvider
     * @param string $expected
     * @param int|string $input
     */
    public function testItReturnsInputNumbersAsEuro($expected, $input)
    {
        $this->assertIsTransformedTo($expected, $input);
    }

    /**
     * @return array[]
     */
    public function numbersOnlyInputDataProvider()
    {
        return [
            ['1,00 €', 100],
            ['1,00 €', '100'],
            ['0,01 €', 1],
            ['0,00 €', 0],
            ['-0,01 €', -1],
            ['-0,11 €', '-11'],
            ['12.345.678,99 €', 1234567899],
            ['12.345.678,99 €', '1234567899'],
        ];
    }
}
