<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Context\ContextBuilder\ContextLocale
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
        $this->assertSame('de_DE', $this->contextLocale->getValue($inputDataSet, $otherContextParts));
    }

    public function testItReturnsTheLocaleFromTheInputArrayIfItIsPresent()
    {
        $inputDataSet = [ContextLocale::CODE => 'xx_XX'];
        $otherContextParts = [];
        $this->assertSame('xx_XX', $this->contextLocale->getValue($inputDataSet, $otherContextParts));
    }

    public function testItReturnsTheLocaleFromTheRequestIfNotExplicitlySpecifiedInInputArray()
    {
        $this->stubRequest->method('getUrlPathRelativeToWebFront')->willReturn('/fr/foo');
        $inputDataSet = [ContextBuilder::REQUEST => $this->stubRequest];
        $otherContextParts = [];
        $this->assertSame('fr_FR', $this->contextLocale->getValue($inputDataSet, $otherContextParts));
    }
}
