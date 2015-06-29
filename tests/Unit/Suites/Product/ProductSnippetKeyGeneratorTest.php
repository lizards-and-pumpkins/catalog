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
    private $productId = 10;
    
    private $testSnippetCode = 'product_detail_view';

    /**
     * @var ProductSnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockContext()
    {
        return $this->getMock(Context::class);
    }

    protected function setUp()
    {
        $this->keyGenerator = new ProductSnippetKeyGenerator($this->testSnippetCode);
    }

    public function testSnippetKeyGeneratorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetKeyGenerator::class, $this->keyGenerator);
    }

    public function testExceptionIsThrownIfTheSnippetCodeIsNotAString()
    {
        $this->setExpectedException(InvalidSnippetCodeException::class);
        new ProductSnippetKeyGenerator(123);
    }

    public function testExceptionIsThrownIfNoProductIdIsSpecified()
    {
        $this->setExpectedException(MissingProductIdException::class);
        $this->keyGenerator->getKeyForContext($this->getMockContext());
    }

    public function testWebsiteAndLanguageContextPartsAreUsed()
    {
        $result = $this->keyGenerator->getContextPartsUsedForKey();
        $this->assertInternalType('array', $result);
        $this->assertContains('website', $result);
        $this->assertContains('language', $result);
    }

    public function testSnippetCodeIsIncludedInTheKey()
    {
        $result = $this->keyGenerator->getKeyForContext($this->getMockContext(), ['product_id' => $this->productId]);
        $this->assertContains($this->testSnippetCode, $result);
    }

    public function testProductIdIsIncludedInTheKey()
    {
        $result = $this->keyGenerator->getKeyForContext($this->getMockContext(), ['product_id' => $this->productId]);
        $this->assertContains((string) $this->productId, $result);
    }

    public function testContextIsIncludedIdInTheKey()
    {
        $testContextId = 'test-context-id';
        $mockContext = $this->getMockContext();
        $mockContext->expects($this->once())->method('getId')->willReturn($testContextId);
        $result = $this->keyGenerator->getKeyForContext($mockContext, ['product_id' => $this->productId]);

        $this->assertContains($testContextId, $result);
    }
}
