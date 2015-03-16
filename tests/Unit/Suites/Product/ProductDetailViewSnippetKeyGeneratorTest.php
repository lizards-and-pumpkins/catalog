<?php

namespace Brera\Product;

use Brera\Context\Context;

/**
 * @covers \Brera\Product\ProductDetailViewSnippetKeyGenerator
 */
class ProductDetailViewSnippetKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductDetailViewSnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContext;

    public function setUp()
    {
        $this->mockContext = $this->getMock(Context::class);
        $this->keyGenerator = new ProductDetailViewSnippetKeyGenerator();
    }

    /**
     * @test
     */
    public function itShouldReturnAString()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $this->assertInternalType(
            'string',
            $this->keyGenerator->getKeyForContext($stubProductId, $this->mockContext)
        );
    }

    /**
     * @test
     * @expectedException \Brera\InvalidSnippetKeyIdentifierException
     */
    public function itShouldOnlyAllowProductIdIdentifiers()
    {
        $notAProductId = 1;

        $this->keyGenerator->getKeyForContext($notAProductId, $this->mockContext);
    }

    /**
     * @test
     */
    public function itShouldReturnTheUsedContextParts()
    {
        $result = $this->keyGenerator->getContextPartsUsedForKey();
        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    /**
     * @test
     */
    public function itShouldDelegateToTheContextToBuildTheContextKey()
    {
        $testContextKey = 'dummy-context-key';
        $this->mockContext->expects($this->once())
            ->method('getIdForParts')
            ->with($this->keyGenerator->getContextPartsUsedForKey())
            ->willReturn($testContextKey);

        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $result = $this->keyGenerator->getKeyForContext($stubProductId, $this->mockContext);
        $this->assertContains($testContextKey, $result);
    }
}
