<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\SnippetKeyGeneratorNotRegisteredException;

/**
 * @covers \LizardsAndPumpkins\SnippetKeyGeneratorLocator
 * @uses   \LizardsAndPumpkins\GenericSnippetKeyGenerator
 */
class SnippetKeyGeneratorLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $locator;

    protected function setUp()
    {
        $this->locator = new SnippetKeyGeneratorLocator();
    }

    public function testExceptionIsThrownIfNonStringSnippetRendererCodeIsPassed()
    {
        $mockSnippetRenderer = $this->getMock(SnippetRenderer::class);
        $this->setExpectedException(InvalidSnippetCodeException::class, 'Expected snippet code to be a string');

        $this->locator->getKeyGeneratorForSnippetCode($mockSnippetRenderer);
    }

    public function testExceptionIsThrownIfSnippetKeyGeneratorNotKnown()
    {
        $this->setExpectedException(SnippetKeyGeneratorNotRegisteredException::class);
        $this->locator->getKeyGeneratorForSnippetCode('test');
    }

    public function testKeyGeneratorForSnippetCodesIsReturned()
    {
        $testSnippetCode = 'test_snippet_code';

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubKeyGenerator */
        $stubKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->locator->register($testSnippetCode, $stubKeyGenerator);

        $this->assertSame($stubKeyGenerator, $this->locator->getKeyGeneratorForSnippetCode($testSnippetCode));
    }

    public function testExceptionIsThrownWhenRegisteringNonStringSnippetCode()
    {
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubKeyGenerator */
        $stubKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->setExpectedException(InvalidSnippetCodeException::class, 'Expected snippet code to be a string');

        $this->locator->register(123, $stubKeyGenerator);
    }

    public function testSameInstanceForSameSnippetCodeIsReturned()
    {
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubKeyGenerator */
        $stubKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->locator->register('test', $stubKeyGenerator);
        $result1 = $this->locator->getKeyGeneratorForSnippetCode('test');
        $result2 = $this->locator->getKeyGeneratorForSnippetCode('test');

        $this->assertSame($result1, $result2);
    }

    public function testDifferentInstancesAreReturnedForDifferentSnippetCodes()
    {
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubKeyGeneratorA */
        $stubKeyGeneratorA = $this->getMock(SnippetKeyGenerator::class);
        $this->locator->register('test1', $stubKeyGeneratorA);

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubKeyGeneratorB */
        $stubKeyGeneratorB = $this->getMock(SnippetKeyGenerator::class);
        $this->locator->register('test2', $stubKeyGeneratorB);

        $result1 = $this->locator->getKeyGeneratorForSnippetCode('test1');
        $result2 = $this->locator->getKeyGeneratorForSnippetCode('test2');

        $this->assertNotSame($result1, $result2);
    }
}
