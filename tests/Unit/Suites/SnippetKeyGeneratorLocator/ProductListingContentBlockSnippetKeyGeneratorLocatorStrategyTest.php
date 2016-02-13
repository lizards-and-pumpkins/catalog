<?php

namespace LizardsAndPumpkins\SnippetKeyGeneratorLocator;

use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\Exception\SnippetCodeCanNotBeProcessedException;

/**
 * @covers \LizardsAndPumpkins\SnippetKeyGeneratorLocator\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 */
class ProductListingContentBlockSnippetKeyGeneratorLocatorStrategyTest extends \PHPUnit_Framework_TestCase
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
        $testKeyGeneratorFactoryClosure = function () {
            return $this->stubSnippetKeyGenerator;
        };
        $this->strategy = new ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy(
            $testKeyGeneratorFactoryClosure
        );
    }

    public function testSnippetKeyGeneratorLocatorStrategyInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $this->strategy);
    }

    public function testFalseIsReturnedIfSnippetCodeIsNotSupported()
    {
        $unsupportedSnippetCode = 'foo';
        $this->assertFalse($this->strategy->canHandle($unsupportedSnippetCode));
    }

    public function testTrueIsReturnedIfSnippetCodeIsSupported()
    {
        $snippetCode = 'product_listing_content_block_foo';
        $this->assertTrue($this->strategy->canHandle($snippetCode));
    }

    public function testExceptionIsThrownDuringAttemptToSnippetKeyGeneratorForUnsupportedSnippetCode()
    {
        $unsupportedSnippetCode = 'foo';
        $this->expectException(SnippetCodeCanNotBeProcessedException::class);
        $this->strategy->getKeyGeneratorForSnippetCode($unsupportedSnippetCode);
    }

    public function testSnippetKeyGeneratorIsReturned()
    {
        $snippetCode = 'product_listing_content_block_foo';
        $result = $this->strategy->getKeyGeneratorForSnippetCode($snippetCode);
        $this->assertSame($this->stubSnippetKeyGenerator, $result);
    }
}
