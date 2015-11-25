<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\DataVersion;

/**
 * @covers \LizardsAndPumpkins\Context\ContextBuilder\ContextVersion
 */
class ContextVersionTest extends \PHPUnit_Framework_TestCase
{
    private $testVersionString = '1234';
    
    /**
     * @var ContextVersion
     */
    private $contextVersion;

    protected function setUp()
    {
        /** @var DataVersion|\PHPUnit_Framework_MockObject_MockObject $stubDataVersion */
        $stubDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $stubDataVersion->method('__toString')->willReturn($this->testVersionString);
        $this->contextVersion = new ContextVersion($stubDataVersion);
    }

    public function testItIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextVersion);
    }

    public function testItReturnsTheCode()
    {
        $this->assertSame(ContextVersion::CODE, $this->contextVersion->getCode());
    }

    public function testItReturnsTheVersionFromTheInputArrayIfPresent()
    {
        $this->assertSame('1.0', $this->contextVersion->getValue([ContextVersion::CODE => '1.0']));
    }

    public function testItReturnsTheInjectedDataVersionValueIfTheInputContainsNoVersion()
    {
        $this->assertSame($this->testVersionString, $this->contextVersion->getValue([]));
    }
}
