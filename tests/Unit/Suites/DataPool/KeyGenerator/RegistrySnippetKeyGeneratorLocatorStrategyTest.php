<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy
 * @covers \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class RegistrySnippetKeyGeneratorLocatorStrategyTest extends TestCase
{
    /**
     * @var RegistrySnippetKeyGeneratorLocatorStrategy
     */
    private $strategy;

    /**
     * @var SnippetKeyGenerator|MockObject
     */
    private $stubSnippetKeyGenerator;

    final protected function setUp(): void
    {
        $this->strategy = new RegistrySnippetKeyGeneratorLocatorStrategy;
        $this->stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
    }

    public function testSnippetKeyGeneratorLocatorStrategyInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $this->strategy);
    }

    public function testFalseIsReturnedIfSnippetCodeCanNotBeHandled(): void
    {
        $unsupportedSnippetCode = 'foo';
        $this->assertFalse($this->strategy->canHandle($unsupportedSnippetCode));
    }

    public function testTrueIsReturnedIfSnippetCodeCanBeHandled(): void
    {
        $snippetCode = 'foo';
        $testClosure = function () {
            // intentionally left empty
        };

        $this->strategy->register($snippetCode, $testClosure);

        $this->assertTrue($this->strategy->canHandle($snippetCode));
    }

    public function testExceptionIsThrownDuringAttemptToLocateSnippetKeyGeneratorForUnsupportedSnippetCode(): void
    {
        $unsupportedSnippetCode = 'foo';
        $this->expectException(SnippetCodeCanNotBeProcessedException::class);
        $this->strategy->getKeyGeneratorForSnippetCode($unsupportedSnippetCode);
    }

    public function testExceptionIsThrownIfNonStringSnippetRendererCodeIsPassed(): void
    {
        $this->expectException(\TypeError::class);
        $this->strategy->getKeyGeneratorForSnippetCode(new \stdClass());
    }

    /**
     * @dataProvider emptySnippetCodeDataProvider
     */
    public function testExceptionIsThrownIfEmptyStringSnippetRendererCodeIsPassed(string $emptySnippetCode): void
    {
        $this->expectException(InvalidSnippetCodeException::class);
        $this->expectExceptionMessage('Snippet code must not be empty.');
        $this->strategy->getKeyGeneratorForSnippetCode($emptySnippetCode);
    }

    /**
     * @return array[]
     */
    public function emptySnippetCodeDataProvider() : array
    {
        return [
            [''],
            [' '],
        ];
    }

    public function testKeyGeneratorForSnippetCodesIsReturned(): void
    {
        $snippetCode = 'foo';
        $testClosure = function () {
            return $this->stubSnippetKeyGenerator;
        };

        $this->strategy->register($snippetCode, $testClosure);

        $this->assertSame($this->stubSnippetKeyGenerator, $this->strategy->getKeyGeneratorForSnippetCode($snippetCode));
    }

    public function testExceptionIsThrownWhenRegisteringEmptyStringSnippetCode(): void
    {
        $invalidSnippetCode = '';
        $testClosure = function () {
            // intentionally left empty
        };

        $this->expectException(InvalidSnippetCodeException::class);

        $this->strategy->register($invalidSnippetCode, $testClosure);
    }

    public function testSameInstanceForSameSnippetCodeIsReturned(): void
    {
        $snippetCode = 'foo';
        $stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $testClosure = function () use ($stubSnippetKeyGenerator) {
            return $stubSnippetKeyGenerator;
        };

        $this->strategy->register($snippetCode, $testClosure);

        $result1 = $this->strategy->getKeyGeneratorForSnippetCode($snippetCode);
        $result2 = $this->strategy->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertSame($result1, $result2);
    }

    public function testDifferentInstancesAreReturnedForDifferentSnippetCodes(): void
    {
        $snippetCodeA = 'foo';
        $stubSnippetKeyGeneratorA = $this->createMock(SnippetKeyGenerator::class);
        $testClosureA = function () use ($stubSnippetKeyGeneratorA) {
            return $stubSnippetKeyGeneratorA;
        };
        $this->strategy->register($snippetCodeA, $testClosureA);

        $snippetCodeB = 'bar';
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
