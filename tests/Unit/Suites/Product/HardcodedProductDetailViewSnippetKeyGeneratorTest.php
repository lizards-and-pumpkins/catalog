<?php

namespace Brera\PoC\Product;

use Brera\PoC\Environment;

class HardcodedProductDetailViewSnippetKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HardcodedProductDetailViewSnippetKeyGenerator
     */
    private $keyGenerator;

    public function setUp()
    {
        $this->keyGenerator = new HardcodedProductDetailViewSnippetKeyGenerator();
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

        $this->assertInternalType('string', $this->keyGenerator->getKeyForEnvironment($stubProductId, $mockEnvironment));
    }

    /**
     * @test
     * @expectedException \Brera\PoC\InvalidSnippetKeyIdentifierException
     */
    public function itShouldOnlyAllowProductIdIdentifiers()
    {
        $notAProductId = 1;
        $mockEnvironment = $this->getMock(Environment::class);

        $this->keyGenerator->getKeyForEnvironment($notAProductId, $mockEnvironment);
    }
}
