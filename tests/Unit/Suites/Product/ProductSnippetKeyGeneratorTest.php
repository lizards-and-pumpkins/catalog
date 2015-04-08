<?php


namespace Brera\Product;

use Brera\Context\Context;
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

    /**
     * @test
     */
    public function itShouldImplementTheSnippetKeyGeneratorInterface()
    {
        $this->assertInstanceOf(SnippetKeyGenerator::class, $this->keyGenerator);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidSnippetCodeException
     */
    public function itShouldThrowAnExceptionIfTheSnippetCodeIsNotAString()
    {
        new ProductSnippetKeyGenerator(123);
    }

    /**
     * @test
     * @expectedException \Brera\Product\MissingProductIdException
     */
    public function itShouldThrowAnExceptionIfNoProductIdIsSpecified()
    {
        $this->keyGenerator->getKeyForContext($this->getMockContext());
    }

    /**
     * @test
     */
    public function itShouldUseWebsiteAndLanguageContextParts()
    {
        $result = $this->keyGenerator->getContextPartsUsedForKey();
        $this->assertInternalType('array', $result);
        $this->assertContains('website', $result);
        $this->assertContains('language', $result);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheSnippetCodeInTheKey()
    {
        $result = $this->keyGenerator->getKeyForContext($this->getMockContext(), ['product_id' => $this->productId]);
        $this->assertContains($this->testSnippetCode, $result);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheProductIdInTheKey()
    {
        $result = $this->keyGenerator->getKeyForContext($this->getMockContext(), ['product_id' => $this->productId]);
        $this->assertContains((string) $this->productId, $result);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheContextIdInTheKey()
    {
        $testContextId = 'test-context-id';
        $mockContext = $this->getMockContext();
        $mockContext->expects($this->once())->method('getId')->willReturn($testContextId);
        $result = $this->keyGenerator->getKeyForContext($mockContext, ['product_id' => $this->productId]);
        $this->assertContains($testContextId, $result);
    }
}
