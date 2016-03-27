<?php

namespace LizardsAndPumpkins\Context\Locale;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\Locale\ContextLocale;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Context\Locale\ContextLocale
 */
class ContextLocaleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextLocale
     */
    private $contextLocale;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    protected function setUp()
    {
        $this->contextLocale = new ContextLocale();
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testItIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextLocale);
    }

    public function testItReturnsTheCode()
    {
        $this->assertSame(ContextLocale::CODE, $this->contextLocale->getCode());
    }

    public function testItReturnsTheDefaultLocaleIfItCanNotBeDeterminedFromTheInputDataSets()
    {
        $inputDataSet = [];
        $otherContextParts = [];

        $this->assertSame('fr_FR', $this->contextLocale->getValue($inputDataSet, $otherContextParts));
    }

    /**
     * @dataProvider urlPathWithoutLocaleProvider
     * @param string $urlPathRelativeToWebFront
     */
    public function testItReturnsTheDefaultLocaleIfItCanNotBeDeterminedFromRequest($urlPathRelativeToWebFront)
    {
        $this->stubRequest->method('getUrlPathRelativeToWebFront')->willReturn($urlPathRelativeToWebFront);
        $inputDataSet = [ContextBuilder::REQUEST => $this->stubRequest];
        $otherContextParts = [];

        $this->assertSame('fr_FR', $this->contextLocale->getValue($inputDataSet, $otherContextParts));
    }

    /**
     * @return array[]
     */
    public function urlPathWithoutLocaleProvider()
    {
        return [
            ['foo'],
            ['enuresis'],
            [''],
        ];
    }

    public function testItReturnsTheLocaleFromTheInputArrayIfItIsPresent()
    {
        $inputDataSet = [ContextLocale::CODE => 'xx_XX'];
        $otherContextParts = [];

        $this->assertSame('xx_XX', $this->contextLocale->getValue($inputDataSet, $otherContextParts));
    }

    /**
     * @dataProvider urlPathWithLocaleProvider
     * @param string $urlPathRelativeToWebFront
     */
    public function testItReturnsTheLocaleFromTheRequestIfNotExplicitlySpecifiedInInputArray($urlPathRelativeToWebFront)
    {
        $this->stubRequest->method('getUrlPathRelativeToWebFront')->willReturn($urlPathRelativeToWebFront);
        $inputDataSet = [ContextBuilder::REQUEST => $this->stubRequest];
        $otherContextParts = [];

        $this->assertSame('en_US', $this->contextLocale->getValue($inputDataSet, $otherContextParts));
    }

    /**
     * @return array[]
     */
    public function urlPathWithLocaleProvider()
    {
        return [
            ['en/foo'],
            ['en'],
        ];
    }
}
