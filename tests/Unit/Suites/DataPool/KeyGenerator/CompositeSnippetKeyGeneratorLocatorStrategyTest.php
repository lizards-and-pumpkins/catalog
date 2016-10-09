<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 */
class CompositeSnippetKeyGeneratorLocatorStrategyTest extends \PHPUnit_Framework_TestCase
{
    private $supportedSnippetCode = 'bar';

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGenetator;

    /**
     * @var CompositeSnippetKeyGeneratorLocatorStrategy
     */
    private $strategy;

    protected function setUp()
    {
        $this->stubSnippetKeyGenetator = $this->createMock(SnippetKeyGenerator::class);

        $stubSnippetKeyGeneratorLocatorStrategy = $this->createMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocatorStrategy->method('canHandle')->willReturnCallback(function (string $code) {
            return $code === $this->supportedSnippetCode;
        });
        $stubSnippetKeyGeneratorLocatorStrategy->method('getKeyGeneratorForSnippetCode')->willReturnMap([
            [$this->supportedSnippetCode, $this->stubSnippetKeyGenetator]
        ]);

        $this->strategy = new CompositeSnippetKeyGeneratorLocatorStrategy($stubSnippetKeyGeneratorLocatorStrategy);
    }

    public function testSnippetKeyGeneratorLocatorStrategyInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $this->strategy);
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
        $this->expectException(SnippetCodeCanNotBeProcessedException::class);
        $this->strategy->getKeyGeneratorForSnippetCode($unsupportedSnippetCode);
    }

    public function testFirstSnippetKeyGeneratorWhichCanHandleSnippetCodeIsReturned()
    {
        $result = $this->strategy->getKeyGeneratorForSnippetCode($this->supportedSnippetCode);
        $this->assertSame($this->stubSnippetKeyGenetator, $result);
    }
}
