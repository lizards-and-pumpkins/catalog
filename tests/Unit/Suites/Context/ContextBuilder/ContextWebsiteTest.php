<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\Exception\UnableToDetermineContextWebsiteException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\WebsiteMap;

/**
 * @covers \LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite
 */
class ContextWebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextWebsite
     */
    private $contextWebsite;

    /**
     * @var WebsiteMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubWebsiteMap;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    protected function setUp()
    {
        $this->stubWebsiteMap = $this->getMock(WebsiteMap::class, [], [], '', false);
        $this->contextWebsite = new ContextWebsite($this->stubWebsiteMap);
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testItIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextWebsite);
    }

    public function testItReturnsTheWebsiteCode()
    {
        $this->assertSame(ContextWebsite::CODE, $this->contextWebsite->getCode());
    }

    public function testItThrowsAnExceptionIfTheWebsiteCanNotBeDeterminedFromTheInputArray()
    {
        $this->setExpectedException(
            UnableToDetermineContextWebsiteException::class,
            'Unable to determine context website because neither the ' .
            'website nor the request are set in the input array.'
        );
        $this->contextWebsite->getValue([], []);
    }

    public function testItReturnsTheWebsiteIfPresentInTheInput()
    {
        $this->assertSame('webA', $this->contextWebsite->getValue([ContextWebsite::CODE => 'webA'], []));
        $this->assertSame('webB', $this->contextWebsite->getValue([ContextWebsite::CODE => 'webB'], []));
    }

    public function testItReturnsTheWebsiteBasedOnTheRequestIfNotExplicitlySet()
    {
        $this->stubWebsiteMap->method('getCodeByHost')->willReturn('web');
        
        $this->stubRequest->method('getHost')->willReturn('example.com');
        
        $this->assertSame('web', $this->contextWebsite->getValue([ContextBuilder::REQUEST => $this->stubRequest], []));
    }
}
