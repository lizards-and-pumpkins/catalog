<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\Http\HttpRequest;

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
        $this->assertSame('de_DE', $this->contextLocale->getValue([]));
    }

    public function testItReturnsTheLocaleFromTheInputArrayIfItIsPresent()
    {
        $this->assertSame('xx_XX', $this->contextLocale->getValue([ContextLocale::CODE => 'xx_XX']));
    }

    public function testItReturnsTheLocaleFromTheRequestIfNotExplicitlySpecifiedInInputArray()
    {
        $this->stubRequest->method('getUrlPathRelativeToWebFront')->willReturn('/fr/foo');
        $this->assertSame('fr_FR', $this->contextLocale->getValue(['request' => $this->stubRequest]));
    }
}
