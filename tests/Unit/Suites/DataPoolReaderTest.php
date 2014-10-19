<?php

namespace Brera\PoC\KeyValue;

use Brera\PoC\Product\ProductId;
use Brera\PoC\Http\HttpUrl;

/**
 * @covers \Brera\PoC\KeyValue\DataPoolReader
 * @uses \Brera\PoC\Product\ProductId
 * @uses \Brera\PoC\Http\HttpUrl
 */
class DataPoolReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keyValueStore;

    /**
     * @var KeyValueStoreKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keyValueStoreKeyGenerator;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    protected function setUp()
    {
        $this->keyValueStore = $this->getMock(KeyValueStore::class);
        $this->keyValueStoreKeyGenerator = $this->getMock(KeyValueStoreKeyGenerator::class);

        $this->dataPoolReader = new DataPoolReader($this->keyValueStore, $this->keyValueStoreKeyGenerator);
    }

    /**
     * @test
     */
    public function shouldReturnPoCProductHtmlBasedOnKeyFromKeyValueStorage()
    {
        $value = '<p>html</p>';

        $productId = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->keyValueStoreKeyGenerator->expects($this->once())
            ->method('createPoCProductHtmlKey')
            ->willReturn((string) $productId);

        $this->keyValueStore->expects($this->once())
            ->method('get')
            ->willReturn($value);

        $html = $this->dataPoolReader->getPoCProductHtml($productId);

        $this->assertEquals($value, $html);
    }

    /**
     * @test
     */
    public function itShouldReturnProductIdBySeoUrl()
    {
        $urlString = 'http://example.com/path';
        $url = HttpUrl::fromString($urlString);

        $key = 'seo_url_' . $urlString;
        $value = 'test';

        $this->keyValueStoreKeyGenerator->expects($this->once())
            ->method('createPoCProductSeoUrlToIdKey')
            ->willReturn($key);

        $this->keyValueStore->expects($this->once())
            ->method('get')
            ->willReturn($value);

        $productId = $this->dataPoolReader->getProductIdBySeoUrl($url);

        $this->assertEquals($value, $productId);
    }

    /**
     * @test
     */
    public function itShouldReturnIfTheSeoUrlKeyExists()
    {
        $urlString = 'http://example.com/path';
        $url = HttpUrl::fromString($urlString);

        $key = 'seo_url_' . $urlString;

        $this->keyValueStoreKeyGenerator->expects($this->once())
            ->method('createPoCProductSeoUrlToIdKey')
            ->willReturn($key);

        $this->keyValueStore->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $this->assertTrue($this->dataPoolReader->hasProductSeoUrl($url));
    }
}
