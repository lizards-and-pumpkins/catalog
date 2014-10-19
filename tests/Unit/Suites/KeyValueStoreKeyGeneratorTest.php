<?php

namespace Brera\PoC\KeyValue;

use Brera\PoC\Product\ProductId;
use Brera\PoC\Http\HttpUrl;

/**
 * Class KeyValueStoreKeyGeneratorTest
 *
 * @package Brera\PoC
 * @covers  \Brera\PoC\KeyValue\KeyValueStoreKeyGenerator
 * @uses    \Brera\PoC\Http\HttpUrl
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
     *
     * write data provider which provides name and create function so we just need one method to test _ALL_ key methods
     */
    public function itShouldGenerateTwoDifferentKeysForDifferentProductIds()
    {
        $productId1 = $this->createProductId('1');
        $productId2 = $this->createProductId('2');

        $key1 = $this->keyGenerator->createPoCProductHtmlKey($productId1);
        $key2 = $this->keyGenerator->createPoCProductHtmlKey($productId2);

        $this->assertFalse($key1 == $key2);
    }

    /**
     * @test
     */
    public function itShouldGenerateAStringAsPoCProductSeoUrlToIdKey()
    {
        /* @var $url HttpUrl */
        $url = HttpUrl::fromString('http://example.com/path');

        $this->assertInternalType('string',
            $this->keyGenerator->createPoCProductSeoUrlToIdKey($url)
        );
    }

    /**
     * @test
     */
    public function itShouldGenerateTwoDifferentKeysForPoCProductSeoUrlToIdKey()
    {
        /* @var $url1 HttpUrl */
        $url1 = HttpUrl::fromString('http://example.com/path1');
        /* @var $url2 HttpUrl */
        $url2 = HttpUrl::fromString('http://example.com/path2');

        $key1 = $this->keyGenerator->createPoCProductSeoUrlToIdKey($url1);
        $key2 = $this->keyGenerator->createPoCProductSeoUrlToIdKey($url2);

        $this->assertFalse($key1 == $key2);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createProductId($id)
    {
        $productId1 = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productId1->expects($this->any())
            ->method('__toString')
            ->willReturn($id);

        return $productId1;
    }
}
