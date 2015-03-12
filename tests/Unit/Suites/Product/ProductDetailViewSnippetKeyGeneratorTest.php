<?php

namespace Brera\Product;

use Brera\Context\Context;

/**
 * @covers \Brera\Product\ProductSnippetKeyGenerator
 */
class ProductDetailViewSnippetKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSnippetKeyGenerator
     */
    private $keyGenerator;

    public function setUp()
    {
        $this->keyGenerator = new ProductSnippetKeyGenerator();
    }

    /**
     * @test
     */
    public function itShouldReturnAString()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class);

        $result = $this->keyGenerator->getKeyForContext('foo', $stubProductId, $stubContext);

        $this->assertInternalType('string', $result);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidSnippetKeyIdentifierException
     */
    public function itShouldOnlyAllowProductIdIdentifiers()
    {
        $notAProductId = 1;
        $mockContext = $this->getMock(Context::class);

        $this->keyGenerator->getKeyForContext('foo', $notAProductId, $mockContext);
    }
}
