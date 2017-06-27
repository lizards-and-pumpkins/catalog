<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\Import\SnippetCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class CompositeSnippetKeyGeneratorLocatorStrategyTest extends TestCase
{
    /**
     * @var SnippetCode
     */
    private $supportedSnippetCode;

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
        $this->supportedSnippetCode = new SnippetCode('bar');
        $this->stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);

        $stubSnippetKeyGeneratorLocatorStrategy = $this->createMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocatorStrategy->method('canHandle')->willReturnCallback(function (SnippetCode $code) {
            return $code === $this->supportedSnippetCode;
        });
        $stubSnippetKeyGeneratorLocatorStrategy->method('getKeyGeneratorForSnippetCode')->willReturnMap([
            [$this->supportedSnippetCode, $this->stubSnippetKeyGenerator]
        ]);

        $this->strategy = new CompositeSnippetKeyGeneratorLocatorStrategy($stubSnippetKeyGeneratorLocatorStrategy);
    }

    public function testSnippetKeyGeneratorLocatorStrategyInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $this->strategy);
    }

    public function testFalseIsReturnedIfNoUnderlyingStrategyCanHandleSnippetCode()
    {
        $unsupportedSnippetCode = new SnippetCode('foo');
        $this->assertFalse($this->strategy->canHandle($unsupportedSnippetCode));
    }

    public function testTrueIsReturnedIfSnippetCodeCanBeHandled()
    {
        $this->assertTrue($this->strategy->canHandle($this->supportedSnippetCode));
    }

    public function testExceptionIsThrownDuringAttemptToSnippetKeyGeneratorForUnsupportedSnippetCode()
    {
        $unsupportedSnippetCode = new SnippetCode('foo');
        $this->expectException(SnippetCodeCanNotBeProcessedException::class);
        $this->strategy->getKeyGeneratorForSnippetCode($unsupportedSnippetCode);
    }

    public function testFirstSnippetKeyGeneratorWhichCanHandleSnippetCodeIsReturned()
    {
        $result = $this->strategy->getKeyGeneratorForSnippetCode($this->supportedSnippetCode);
        $this->assertSame($this->stubSnippetKeyGenerator, $result);
    }
}
