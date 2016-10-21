<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy
 * @covers \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class RegistrySnippetKeyGeneratorLocatorStrategyTest extends \PHPUnit_Framework_TestCase
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
        $unsupportedSnippetCode = 'foo';
        $this->assertFalse($this->strategy->canHandle($unsupportedSnippetCode));
    }

    public function testTrueIsReturnedIfSnippetCodeCanBeHandled()
    {
        $snippetCode = 'foo';
        $testClosure = function () {
            // intentionally left empty
        };

        $this->strategy->register($snippetCode, $testClosure);

        $this->assertTrue($this->strategy->canHandle($snippetCode));
    }

    public function testExceptionIsThrownDuringAttemptToLocateSnippetKeyGeneratorForUnsupportedSnippetCode()
    {
        $unsupportedSnippetCode = 'foo';
        $this->expectException(SnippetCodeCanNotBeProcessedException::class);
        $this->strategy->getKeyGeneratorForSnippetCode($unsupportedSnippetCode);
    }

    public function testExceptionIsThrownIfNonStringSnippetRendererCodeIsPassed()
    {
        $this->expectException(\TypeError::class);
        $this->strategy->getKeyGeneratorForSnippetCode(new \stdClass());
    }

    /**
     * @dataProvider emptySnippetCodeDataProvider
     */
    public function testExceptionIsThrownIfEmptyStringSnippetRendererCodeIsPassed(string $emptySnippetCode)
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

    public function testKeyGeneratorForSnippetCodesIsReturned()
    {
        $snippetCode = 'foo';
        $testClosure = function () {
            return $this->stubSnippetKeyGenerator;
        };

        $this->strategy->register($snippetCode, $testClosure);

        $this->assertSame($this->stubSnippetKeyGenerator, $this->strategy->getKeyGeneratorForSnippetCode($snippetCode));
    }

    public function testExceptionIsThrownWhenRegisteringEmptyStringSnippetCode()
    {
        $invalidSnippetCode = '';
        $testClosure = function () {
            // intentionally left empty
        };

        $this->expectException(InvalidSnippetCodeException::class);

        $this->strategy->register($invalidSnippetCode, $testClosure);
    }

    public function testSameInstanceForSameSnippetCodeIsReturned()
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

    public function testDifferentInstancesAreReturnedForDifferentSnippetCodes()
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
