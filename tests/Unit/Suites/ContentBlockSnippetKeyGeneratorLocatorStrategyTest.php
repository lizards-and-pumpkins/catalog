<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\SnippetCodeCanNotBeProcessedException;

/**
 * @covers \LizardsAndPumpkins\ContentBlockSnippetKeyGeneratorLocatorStrategy
 */
class ContentBlockSnippetKeyGeneratorLocatorStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Closure
     */
    private $testClosure;

    /**
     * @var ContentBlockSnippetKeyGeneratorLocatorStrategy
     */
    private $strategy;

    protected function setUp()
    {
        $this->testClosure = function () { };
        $this->strategy = new ContentBlockSnippetKeyGeneratorLocatorStrategy($this->testClosure);
    }

    public function testSnippetKeyGeneratorLocatorStrategyInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGeneratorLocatorstrategy::class, $this->strategy);
    }

    public function testFalseIsReturnedIfSnippetCodeIsNotSupported()
    {
        $unsupportedSnippetCode = 'foo';
        $this->assertFalse($this->strategy->canHandle($unsupportedSnippetCode));
    }

    public function testTrueIsReturnedIfSnippetCodeIsSupported()
    {
        $snippetCode = 'content_block_foo';
        $this->assertTrue($this->strategy->canHandle($snippetCode));
    }
    
    public function testExceptionIsThrownDuringAttemptToSnippetKeyGeneratorForUnsupportedSnippetCode()
    {
        $unsupportedSnippetCode = 'foo';
        $this->setExpectedException(SnippetCodeCanNotBeProcessedException::class);
        $this->strategy->getKeyGeneratorForSnippetCode($unsupportedSnippetCode);
    }

    public function testSnippetKeyGeneratorIsReturned()
    {
        $snippetCode = 'content_block_foo';
        $result = $this->strategy->getKeyGeneratorForSnippetCode($snippetCode);
        $this->assertSame($this->testClosure, $result);
    }
}
