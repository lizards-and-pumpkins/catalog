<?php

namespace LizardsAndPumpkins\ContentDelivery\SnippetTransformation;

use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\Exception\NoValidLocaleInContextException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\SnippetTransformation\LocaleAwareEuroPriceSnippetTransformation
 */
class LocaleAwareEuroPriceSnippetTransformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocaleAwareEuroPriceSnippetTransformation
     */
    private $transformation;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContext;

    /**
     * @param string $expected
     * @param int|string|null $input
     * @param string $locale
     */
    private function assertIsTransformedTo($expected, $input, $locale)
    {
        $this->mockContext->method('getValue')->with(ContextLocale::CODE)->willReturn($locale);
        $transformation = $this->transformation;
        $this->assertSame($expected, $transformation($input, $this->mockContext));
    }
    
    protected function setUp()
    {
        $this->transformation = new LocaleAwareEuroPriceSnippetTransformation();
        $this->mockContext = $this->getMock(Context::class);
    }

    public function testItReturnsNullInputAsAnEmptyString()
    {
        $this->assertIsTransformedTo('', null, 'de_DE');
    }

    public function testItReturnsAnEmptyStringForArrayInput()
    {
        $this->assertIsTransformedTo('', [], 'de_DE');
    }

    public function testItReturnsStringsThatNotOnlyContainNumbersAsIs()
    {
        $this->assertIsTransformedTo('123.4', '123.4', 'de_DE');
    }

    public function testItReturnsAnEmptyStringInputAsAnEmptyString()
    {
        $this->assertIsTransformedTo('', '', 'de_DE');
    }

    public function testItThrowsAnExceptionIfTheContextReturnsNoValidLocale()
    {
        $this->setExpectedException(NoValidLocaleInContextException::class, "No valid locale in context");
        call_user_func($this->transformation, 0, $this->mockContext);
    }

    /**
     * @dataProvider validNumberDataProvider
     * @param string|int $input
     * @param string $locale
     * @param string $expected
     */
    public function testItReturnsTheInputAsEuro($input, $locale, $expected)
    {
        $this->assertIsTransformedTo($expected, $input, $locale);
    }

    /**
     * @return array[]
     */
    public function validNumberDataProvider()
    {
        return [
            [100, 'de_DE', '1,00 €'],
            [100, 'en_US', '€1.00'],
            [100000, 'de_DE', '1.000,00 €'],
            [100000, 'en_US', '€1,000.00'],
            [0, 'de_DE', '0,00 €'],
            [1, 'de_DE', '0,01 €'],
            ['1', 'de_DE', '0,01 €'],
            ['-1', 'de_DE', '-0,01 €'],
            [1234567899, 'de_DE', '12.345.678,99 €'],
        ];
    }
}
