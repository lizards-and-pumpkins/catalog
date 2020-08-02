<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\Exception\SnippetCodeCanNotBeProcessedException;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 */
class ProductListingContentBlockSnippetKeyGeneratorLocatorStrategyTest extends TestCase
{
    /**
     * @var SnippetKeyGenerator|MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var ContentBlockSnippetKeyGeneratorLocatorStrategy
     */
    private $strategy;

    final protected function setUp(): void
    {
        $this->stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $testKeyGeneratorFactoryClosure = function () {
            return $this->stubSnippetKeyGenerator;
        };
        $this->strategy = new ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy(
            $testKeyGeneratorFactoryClosure
        );
    }

    public function testSnippetKeyGeneratorLocatorStrategyInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $this->strategy);
    }

    public function testFalseIsReturnedIfSnippetCodeIsNotSupported(): void
    {
        $unsupportedSnippetCode = 'foo';
        $this->assertFalse($this->strategy->canHandle($unsupportedSnippetCode));
    }

    public function testTrueIsReturnedIfSnippetCodeIsSupported(): void
    {
        $snippetCode = 'product_listing_content_block_foo';
        $this->assertTrue($this->strategy->canHandle($snippetCode));
    }

    public function testExceptionIsThrownDuringAttemptToSnippetKeyGeneratorForUnsupportedSnippetCode(): void
    {
        $unsupportedSnippetCode = 'foo';
        $this->expectException(SnippetCodeCanNotBeProcessedException::class);
        $this->strategy->getKeyGeneratorForSnippetCode($unsupportedSnippetCode);
    }

    public function testSnippetKeyGeneratorIsReturned(): void
    {
        $snippetCode = 'product_listing_content_block_foo';
        $result = $this->strategy->getKeyGeneratorForSnippetCode($snippetCode);
        $this->assertSame($this->stubSnippetKeyGenerator, $result);
    }
}
