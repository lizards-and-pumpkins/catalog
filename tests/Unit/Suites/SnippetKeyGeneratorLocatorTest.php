<?php

namespace Brera;

/**
 * @covers \Brera\SnippetKeyGeneratorLocator
 * @uses   \Brera\GenericSnippetKeyGenerator
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

    public function testKeyGeneratorForSnippetCodesAreRegistered()
    {
        $testSnippetCode = 'test_snippet_code';
        $stubKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->locator->register($testSnippetCode, $stubKeyGenerator);

        $this->assertSame($stubKeyGenerator, $this->locator->getKeyGeneratorForSnippetCode($testSnippetCode));
    }

    public function testExceptionIsThrownWhenRegisteringNonStringSnippetCode()
    {
        $stubKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->setExpectedException(InvalidSnippetCodeException::class, 'Expected snippet code to be a string');

        $this->locator->register(123, $stubKeyGenerator);
    }

    public function testSameInstanceForSameSnippetCodeIsReturned()
    {
        $this->locator->register('test', $this->getMock(SnippetKeyGenerator::class));
        $result1 = $this->locator->getKeyGeneratorForSnippetCode('test');
        $result2 = $this->locator->getKeyGeneratorForSnippetCode('test');

        $this->assertSame($result1, $result2);
    }

    public function testDifferentInstancesAreReturnedForDifferentSnippetCodes()
    {
        $this->locator->register('test1', $this->getMock(SnippetKeyGenerator::class));
        $this->locator->register('test2', $this->getMock(SnippetKeyGenerator::class));
        $result1 = $this->locator->getKeyGeneratorForSnippetCode('test1');
        $result2 = $this->locator->getKeyGeneratorForSnippetCode('test2');
        $this->assertNotSame($result1, $result2);
    }
}
