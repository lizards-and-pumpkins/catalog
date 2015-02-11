<?php

namespace Brera\Product;

use Brera\Environment\Environment;

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
        $mockEnvironment = $this->getMock(Environment::class);

        $this->assertInternalType(
            'string',
            $this->keyGenerator->getKeyForEnvironment($stubProductId, $mockEnvironment)
        );
    }

    /**
     * @test
     * @expectedException \Brera\InvalidSnippetKeyIdentifierException
     */
    public function itShouldOnlyAllowProductIdIdentifiers()
    {
        $notAProductId = 1;
        $mockEnvironment = $this->getMock(Environment::class);

        $this->keyGenerator->getKeyForEnvironment($notAProductId, $mockEnvironment);
    }
}
