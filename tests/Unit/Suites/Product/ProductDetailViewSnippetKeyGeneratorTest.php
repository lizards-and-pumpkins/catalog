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

    public function setUp()
    {
        $this->keyGenerator = new ProductDetailViewSnippetKeyGenerator();
    }

    /**
     * @test
     */
    public function itShouldReturnAString()
    {
        $stubProductId = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockContext = $this->getMock(Context::class);

        $this->assertInternalType(
            'string',
            $this->keyGenerator->getKeyForContext($stubProductId, $mockContext)
        );
    }

    /**
     * @test
     * @expectedException \Brera\InvalidSnippetKeyIdentifierException
     */
    public function itShouldOnlyAllowProductIdIdentifiers()
    {
        $notAProductId = 1;
        $mockContext = $this->getMock(Context::class);

        $this->keyGenerator->getKeyForContext($notAProductId, $mockContext);
    }
}
