<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\Import\SnippetCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class RegistrySnippetKeyGeneratorLocatorStrategyTest extends TestCase
{
    /**
     * @var RegistrySnippetKeyGeneratorLocatorStrategy
     */
    private $strategy;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGenerator;

    protected function setUp()
    {
        $this->strategy = new RegistrySnippetKeyGeneratorLocatorStrategy;
        $this->stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
    }

    public function testSnippetKeyGeneratorLocatorStrategyInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $this->strategy);
    }

    public function testFalseIsReturnedIfSnippetCodeCanNotBeHandled()
    {
        $unsupportedSnippetCode = new SnippetCode('foo');
        $this->assertFalse($this->strategy->canHandle($unsupportedSnippetCode));
    }

    public function testTrueIsReturnedIfSnippetCodeCanBeHandled()
    {
        $snippetCode = new SnippetCode('foo');
        $testClosure = function () {
            // intentionally left empty
        };

        $this->strategy->register($snippetCode, $testClosure);

        $this->assertTrue($this->strategy->canHandle($snippetCode));
    }

    public function testExceptionIsThrownDuringAttemptToLocateSnippetKeyGeneratorForUnsupportedSnippetCode()
    {
        $unsupportedSnippetCode = new SnippetCode('foo');
        $this->expectException(SnippetCodeCanNotBeProcessedException::class);
        $this->strategy->getKeyGeneratorForSnippetCode($unsupportedSnippetCode);
    }

    public function testExceptionIsThrownIfNonStringSnippetRendererCodeIsPassed()
    {
        $this->expectException(\TypeError::class);
        $this->strategy->getKeyGeneratorForSnippetCode(new \stdClass());
    }

    public function testKeyGeneratorForSnippetCodesIsReturned()
    {
        $snippetCode = new SnippetCode('foo');
        $testClosure = function () {
            return $this->stubSnippetKeyGenerator;
        };

        $this->strategy->register($snippetCode, $testClosure);

        $this->assertSame($this->stubSnippetKeyGenerator, $this->strategy->getKeyGeneratorForSnippetCode($snippetCode));
    }

    public function testSameInstanceForSameSnippetCodeIsReturned()
    {
        $snippetCode = new SnippetCode('foo');
        $stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $testClosure = function () use ($stubSnippetKeyGenerator) {
            return $stubSnippetKeyGenerator;
        };

        $this->strategy->register($snippetCode, $testClosure);

        $result1 = $this->strategy->getKeyGeneratorForSnippetCode($snippetCode);
        $result2 = $this->strategy->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertSame($result1, $result2);
    }

    public function testDifferentInstancesAreReturnedForDifferentSnippetCodes()
    {
        $snippetCodeA = new SnippetCode('foo');
        $stubSnippetKeyGeneratorA = $this->createMock(SnippetKeyGenerator::class);
        $testClosureA = function () use ($stubSnippetKeyGeneratorA) {
            return $stubSnippetKeyGeneratorA;
        };
        $this->strategy->register($snippetCodeA, $testClosureA);

        $snippetCodeB = new SnippetCode('bar');
        $stubSnippetKeyGeneratorB = $this->createMock(SnippetKeyGenerator::class);
        $testClosureB = function () use ($stubSnippetKeyGeneratorB) {
            return $stubSnippetKeyGeneratorB;
        };
        $this->strategy->register($snippetCodeB, $testClosureB);

        $resultA = $this->strategy->getKeyGeneratorForSnippetCode($snippetCodeA);
        $resultB = $this->strategy->getKeyGeneratorForSnippetCode($snippetCodeB);

        $this->assertNotSame($resultA, $resultB);
    }
}
