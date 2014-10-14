<?php

namespace Brera\Poc;

/**
 * Class KeyValueStoreKeyGeneratorTest
 *
 * @package Brera\Poc
 * @covers  \Brera\Poc\KeyValueStoreKeyGenerator
 * @uses \Brera\PoC\Url
 */
class KeyValueStoreKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KeyValueStoreKeyGenerator
     */
    private $keyGenerator;

    public function setUp()
    {
        $this->keyGenerator = new KeyValueStoreKeyGenerator();
    }

    /**
     * @test
     */
    public function itShouldGenerateAStringAsPoCProductHtmlKey()
    {
        /* @var $productId ProductId */
        $productId = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInternalType('string',
            $this->keyGenerator->createPoCProductHtmlKey($productId)
        );
    }

    /**
     * @test
     */
    public function itShouldGenerateTwoDifferentKeysForDifferentProductIds()
    {
        $productId1 = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productId2 = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productId1->expects($this->any())
            ->method('__toString')
            ->willReturn('1');
        $productId2->expects($this->any())
            ->method('__toString')
            ->willReturn('2');

        $key1 = $this->keyGenerator->createPoCProductHtmlKey($productId1);
        $key2 = $this->keyGenerator->createPoCProductHtmlKey($productId2);

        $this->assertFalse($key1 == $key2);
    }

    /**
     * @test
     */
    public function itShouldGenerateAStringAsPocProductSeoUrlToIdKey()
    {
        /* @var $url HttpUrl */
        $url = Url::fromString('http://example.com/path');

        $this->assertInternalType('string',
            $this->keyGenerator->createPocProductSeoUrlToIdKey($url)
        );
    }

    /**
     * @test
     */
    public function itShouldGenerateTwoDifferentKeysForPocProductSeoUrlToIdKey()
    {
        /* @var $url1 HttpUrl */
        $url1 = Url::fromString('http://example.com/path1');
        /* @var $url2 HttpUrl */
        $url2 = Url::fromString('http://example.com/path2');

        $key1 = $this->keyGenerator->createPocProductSeoUrlToIdKey($url1);
        $key2 = $this->keyGenerator->createPocProductSeoUrlToIdKey($url2);

        $this->assertFalse($key1 == $key2);
    }

}
