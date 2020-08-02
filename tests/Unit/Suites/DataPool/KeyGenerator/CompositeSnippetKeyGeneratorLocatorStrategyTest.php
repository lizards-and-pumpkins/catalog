<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 */
class CompositeSnippetKeyGeneratorLocatorStrategyTest extends TestCase
{
    private $supportedSnippetCode = 'bar';

    /**
     * @var SnippetKeyGenerator|MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var CompositeSnippetKeyGeneratorLocatorStrategy
     */
    private $strategy;

    final protected function setUp(): void
    {
        $this->stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);

        $stubSnippetKeyGeneratorLocatorStrategy = $this->createMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocatorStrategy->method('canHandle')->willReturnCallback(function (string $code) {
            return $code === $this->supportedSnippetCode;
        });
        $stubSnippetKeyGeneratorLocatorStrategy->method('getKeyGeneratorForSnippetCode')->willReturnMap([
            [$this->supportedSnippetCode, $this->stubSnippetKeyGenerator]
        ]);

        $this->strategy = new CompositeSnippetKeyGeneratorLocatorStrategy($stubSnippetKeyGeneratorLocatorStrategy);
    }

    public function testSnippetKeyGeneratorLocatorStrategyInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $this->strategy);
    }

    public function testFalseIsReturnedIfNoUnderlyingStrategyCanHandleSnippetCode(): void
    {
        $unsupportedSnippetCode = 'foo';
        $this->assertFalse($this->strategy->canHandle($unsupportedSnippetCode));
    }

    public function testTrueIsReturnedIfSnippetCodeCanBeHandled(): void
    {
        $this->assertTrue($this->strategy->canHandle($this->supportedSnippetCode));
    }

    public function testExceptionIsThrownDuringAttemptToSnippetKeyGeneratorForUnsupportedSnippetCode(): void
    {
        $unsupportedSnippetCode = 'foo';
        $this->expectException(SnippetCodeCanNotBeProcessedException::class);
        $this->strategy->getKeyGeneratorForSnippetCode($unsupportedSnippetCode);
    }

    public function testFirstSnippetKeyGeneratorWhichCanHandleSnippetCodeIsReturned(): void
    {
        $result = $this->strategy->getKeyGeneratorForSnippetCode($this->supportedSnippetCode);
        $this->assertSame($this->stubSnippetKeyGenerator, $result);
    }
}
