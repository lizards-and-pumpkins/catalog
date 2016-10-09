<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\DataVersion;

use LizardsAndPumpkins\Context\ContextPartBuilder;

/**
 * @covers \LizardsAndPumpkins\Context\DataVersion\ContextVersion
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
        $stubDataVersion = $this->createMock(DataVersion::class);
        $stubDataVersion->method('__toString')->willReturn($this->testVersionString);
        $this->contextVersion = new ContextVersion($stubDataVersion);
    }

    public function testItIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextVersion);
    }

    public function testItReturnsTheCode()
    {
        $this->assertSame(DataVersion::CONTEXT_CODE, $this->contextVersion->getCode());
    }

    public function testItReturnsTheVersionFromTheInputArrayIfPresent()
    {
        $inputDataSet = [DataVersion::CONTEXT_CODE => '1.0'];
        $this->assertSame('1.0', $this->contextVersion->getValue($inputDataSet));
    }

    public function testItReturnsTheInjectedDataVersionValueIfTheInputContainsNoVersion()
    {
        $inputDataSet = [];
        $this->assertSame($this->testVersionString, $this->contextVersion->getValue($inputDataSet));
    }
}
