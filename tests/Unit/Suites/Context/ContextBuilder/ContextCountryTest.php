<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Context\ContextBuilder\ContextCountry
 */
class ContextCountryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextCountry
     */
    private $contextCountry;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @param string $cookieCountry
     */
    private function setRequestCountry($cookieCountry)
    {
        $json = json_encode(['country' => $cookieCountry]);
        $this->stubRequest->method('getCookieValue')->with(ContextCountry::COOKIE_NAME)->willReturn($json);
        $this->stubRequest->method('hasCookie')->with(ContextCountry::COOKIE_NAME)->willReturn(true);
    }

    protected function setUp()
    {
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->contextCountry = new ContextCountry();
    }

    public function testItIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextCountry);
    }

    public function testItReturnsTheCountryContextPartCode()
    {
        $this->assertSame(ContextCountry::CODE, $this->contextCountry->getCode());
    }

    public function testItReturnsTheDefaultCountryIfNothingIsSpecifiedInTheRequest()
    {
        $inputDataSet = [];
        $otherContextParts = [];
        $this->assertSame('de', $this->contextCountry->getValue($inputDataSet, $otherContextParts));
    }

    public function testItReturnsTheValueFromTheInputDataSetIfPresent()
    {
        $inputDataSet = [ContextCountry::CODE => 'fr'];
        $otherContextParts = [];
        $this->assertSame('fr', $this->contextCountry->getValue($inputDataSet, $otherContextParts));
    }

    public function testItReturnsTheCountryFromTheRequestIfNotPartOfTheInputDataSet()
    {
        $this->setRequestCountry('en');
        $inputDataSet = [ContextBuilder::REQUEST => $this->stubRequest];
        $otherContextParts = [];
        $this->assertSame('en', $this->contextCountry->getValue($inputDataSet, $otherContextParts));
    }

    public function testItPrefersTheExplicitValueIfBothSourcesArePresentInTheInputDataSet()
    {
        $this->setRequestCountry('en');
        $inputDataSet = [
            ContextBuilder::REQUEST => $this->stubRequest,
            ContextCountry::CODE => 'fr'
        ];
        $otherContextParts = [];
        $this->assertSame('fr', $this->contextCountry->getValue($inputDataSet, $otherContextParts));
    }
}
