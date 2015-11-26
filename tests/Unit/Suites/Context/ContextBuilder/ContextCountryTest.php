<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
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

    public function testItReturnsTheCorrectCode()
    {
        $this->assertSame(ContextCountry::CODE, $this->contextCountry->getCode());
    }

    public function testItReturnsTheDefaultCountryIfNothingIsSpecifiedInTheRequest()
    {
        $this->assertSame('fr', $this->contextCountry->getValue([]));
    }

    public function testItReturnsTheValueFromTheInputDataSetIfPresent()
    {
        $this->assertSame('de', $this->contextCountry->getValue([ContextCountry::CODE => 'de']));
    }

    public function testItReturnsTheCountryFromTheRequestIfNotPartOfTheInputDataSet()
    {
        $this->setRequestCountry('en');
        $inputDataSet = [SelfContainedContextBuilder::REQUEST => $this->stubRequest];
        
        $this->assertSame('en', $this->contextCountry->getValue($inputDataSet));
    }

    public function testItPrefersTheExplicitValueIfBothSourcesArePresentInTheInputDataSet()
    {
        $this->setRequestCountry('en');
        $inputDataSet = [
            SelfContainedContextBuilder::REQUEST => $this->stubRequest,
            ContextCountry::CODE => 'de'
        ];
        $this->assertSame('de', $this->contextCountry->getValue($inputDataSet));
    }
}
