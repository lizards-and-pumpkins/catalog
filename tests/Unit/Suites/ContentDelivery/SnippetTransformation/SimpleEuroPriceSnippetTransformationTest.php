<?php


namespace Brera\ContentDelivery\SnippetTransformation;

use Brera\Context\Context;

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
     * @param string $expected
     * @param int $input
     */
    private function assertIsTransformedTo($expected, $input)
    {
        $transformation = $this->transformation;
        $this->assertSame($expected, $transformation($input, $this->stubContext));
    }

    protected function setUp()
    {
        $this->transformation = new SimpleEuroPriceSnippetTransformation();
        $this->stubContext = $this->getMock(Context::class);
    }

    public function testItIsCallable()
    {
        $this->assertInstanceOf(SnippetTransformation::class, $this->transformation);
        $this->assertTrue(is_callable($this->transformation), "Snippet transformations not callable");
    }

    public function testItIgnoresStringInput()
    {
        $this->assertIsTransformedTo('123', '123');
    }

    /**
     * @dataProvider integerInputDataProvider
     */
    public function testItReturnsIntegersInEuro($expected, $input)
    {
        $this->assertIsTransformedTo($expected, $input);
    }

    public function integerInputDataProvider()
    {
        return [
            ['1,00 €', 100],
            ['0,01 €', 1],
            ['0,00 €', 0],
            ['-0,01 €', -1],
            ['12.345.678,99 €', 1234567899],
        ];
    }
}
