<?php

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\InvalidSnippetCodeException;
use LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Import\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy
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
        $this->stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
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
        $stubSnippetRenderer = $this->getMock(SnippetRenderer::class);
        $this->expectException(InvalidSnippetCodeException::class);
        $this->expectExceptionMessage('Expected snippet code to be a string');
        $this->strategy->getKeyGeneratorForSnippetCode($stubSnippetRenderer);
    }

    /**
     * @dataProvider emptySnippetCodeDataProvider
     * @param string $emptySnippetCode
     */
    public function testExceptionIsThrownIfEmptyStringSnippetRendererCodeIsPassed($emptySnippetCode)
    {
        $this->expectException(InvalidSnippetCodeException::class);
        $this->expectExceptionMessage('Snippet code must not be empty');
        $this->strategy->getKeyGeneratorForSnippetCode($emptySnippetCode);
    }

    /**
     * @return array[]
     */
    public function emptySnippetCodeDataProvider()
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

    public function testExceptionIsThrownWhenRegisteringNonStringSnippetCode()
    {
        $invalidSnippetCode = 123;
        $testClosure = function () {
            // intentionally left empty
        };

        $this->expectException(InvalidSnippetCodeException::class);
        $this->expectExceptionMessage('Expected snippet code to be a string');

        $this->strategy->register($invalidSnippetCode, $testClosure);
    }

    public function testSameInstanceForSameSnippetCodeIsReturned()
    {
        $snippetCode = 'foo';
        $testClosure = function () {
            // intentionally left empty
        };

        $this->strategy->register($snippetCode, $testClosure);

        $result1 = $this->strategy->getKeyGeneratorForSnippetCode($snippetCode);
        $result2 = $this->strategy->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertSame($result1, $result2);
    }

    public function testDifferentInstancesAreReturnedForDifferentSnippetCodes()
    {
        $snippetCodeA = 'foo';
        $stubSnippetKeyGeneratorA = $this->getMock(SnippetKeyGenerator::class);
        $testClosureA = function () use ($stubSnippetKeyGeneratorA) {
            return $stubSnippetKeyGeneratorA;
        };
        $this->strategy->register($snippetCodeA, $testClosureA);

        $snippetCodeB = 'bar';
        $stubSnippetKeyGeneratorB = $this->getMock(SnippetKeyGenerator::class);
        $testClosureB = function () use ($stubSnippetKeyGeneratorB) {
            return $stubSnippetKeyGeneratorB;
        };
        $this->strategy->register($snippetCodeB, $testClosureB);

        $resultA = $this->strategy->getKeyGeneratorForSnippetCode($snippetCodeA);
        $resultB = $this->strategy->getKeyGeneratorForSnippetCode($snippetCodeB);

        $this->assertNotSame($resultA, $resultB);
    }
}
