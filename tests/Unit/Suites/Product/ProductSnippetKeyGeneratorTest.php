<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\InvalidSnippetCodeException;
use Brera\SnippetKeyGenerator;

/**
 * @covers \Brera\Product\ProductSnippetKeyGenerator
 */
class ProductSnippetKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    private $productId = 10;

    /**
     * @var string
     */
    private $dummySnippetCode = 'product_detail_view';

    /**
     * @var string[]
     */
    private $dummyContextParts = ['dummy-context-part'];

    /**
     * @var ProductSnippetKeyGenerator
     */
    private $keyGenerator;

    protected function setUp()
    {
        $this->keyGenerator = new ProductSnippetKeyGenerator($this->dummySnippetCode, $this->dummyContextParts);
    }

    public function testSnippetKeyGeneratorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGenerator::class, $this->keyGenerator);
    }

    public function testExceptionIsThrownIfTheSnippetCodeIsNotAString()
    {
        $this->setExpectedException(InvalidSnippetCodeException::class);
        new ProductSnippetKeyGenerator(123, $this->dummyContextParts);
    }

    public function testExceptionIsThrownIfNoProductIdIsSpecified()
    {
        $this->setExpectedException(MissingProductIdException::class);
        $stubContext = $this->getMock(Context::class);
        $this->keyGenerator->getKeyForContext($stubContext);
    }

    public function testRequiredContextPartsAreReturned()
    {
        $result = $this->keyGenerator->getContextPartsUsedForKey();
        $this->assertSame($this->dummyContextParts, $result);
    }

    public function testSnippetCodeIsIncludedInTheKey()
    {
        $stubContext = $this->getMock(Context::class);
        $result = $this->keyGenerator->getKeyForContext($stubContext, ['product_id' => $this->productId]);

        $this->assertContains($this->dummySnippetCode, $result);
    }

    public function testProductIdIsIncludedInTheKey()
    {
        $stubContext = $this->getMock(Context::class);
        $result = $this->keyGenerator->getKeyForContext($stubContext, ['product_id' => $this->productId]);

        $this->assertContains((string) $this->productId, $result);
    }

    public function testContextIdentifierIsIncludedInReturnedKey()
    {
        $dummyContextId = 'foo';
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getIdForParts')->willReturn($dummyContextId);
        $result = $this->keyGenerator->getKeyForContext($stubContext, ['product_id' => $this->productId]);

        $this->assertContains($dummyContextId, $result);
    }
}
