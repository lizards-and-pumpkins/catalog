<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\SnippetCodeCanNotBeProcessedException;

/**
 * @covers \LizardsAndPumpkins\ContentBlockSnippetKeyGeneratorLocatorStrategy
 */
class ContentBlockSnippetKeyGeneratorLocatorStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var ContentBlockSnippetKeyGeneratorLocatorStrategy
     */
    private $strategy;

    protected function setUp()
    {
        $this->stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $testClosure = function () { return $this->stubSnippetKeyGenerator; };
        $this->strategy = new ContentBlockSnippetKeyGeneratorLocatorStrategy($testClosure);
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
        $this->assertSame($this->stubSnippetKeyGenerator, $result);
    }
}
