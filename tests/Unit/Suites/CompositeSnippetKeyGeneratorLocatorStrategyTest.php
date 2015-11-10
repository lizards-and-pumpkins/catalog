<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\SnippetCodeCanNotBeProcessedException;

/**
 * @covers \LizardsAndPumpkins\CompositeSnippetKeyGeneratorLocatorStrategy
 */
class CompositeSnippetKeyGeneratorLocatorStrategyTest extends \PHPUnit_Framework_TestCase
{
    private $supportedSnippetCode = 'bar';

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var CompositeSnippetKeyGeneratorLocatorStrategy
     */
    private $strategy;

    protected function setUp()
    {
        $this->stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $stubSnippetKeyGeneratorLocatorStrategy = $this->getMock(SnippetKeyGeneratorLocatorStrategy::class);
        $stubSnippetKeyGeneratorLocatorStrategy->method('canHandle')->willReturnMap([
            [$this->supportedSnippetCode, true]
        ]);
        $stubSnippetKeyGeneratorLocatorStrategy->method('getKeyGeneratorForSnippetCode')->willReturnMap([
            [$this->supportedSnippetCode, $this->stubSnippetKeyGenerator]
        ]);

        $this->strategy = new CompositeSnippetKeyGeneratorLocatorStrategy($stubSnippetKeyGeneratorLocatorStrategy);
    }

    public function testSnippetKeyGeneratorLocatorStrategyInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGeneratorLocatorStrategy::class, $this->strategy);
    }

    public function testFalseIsReturnedIfNoUnderlyingStrategyCanHandleSnippetCode()
    {
        $unsupportedSnippetCode = 'foo';
        $this->assertFalse($this->strategy->canHandle($unsupportedSnippetCode));
    }

    public function testTrueIsReturnedIfSnippetCodeCanBeHandled()
    {
        $this->assertTrue($this->strategy->canHandle($this->supportedSnippetCode));
    }

    public function testExceptionIsThrownDuringAttemptToSnippetKeyGeneratorForUnsupportedSnippetCode()
    {
        $unsupportedSnippetCode = 'foo';
        $this->setExpectedException(SnippetCodeCanNotBeProcessedException::class);
        $this->strategy->getKeyGeneratorForSnippetCode($unsupportedSnippetCode);
    }

    public function testFirstSnippetKeyGeneratorWhichCanHandleSnippetCodeIsReturned()
    {
        $result = $this->strategy->getKeyGeneratorForSnippetCode($this->supportedSnippetCode);
        $this->assertSame($this->stubSnippetKeyGenerator, $result);
    }
}
