<?php

namespace Brera\KeyValue;

use Brera\Product\ProductId;
use Brera\Http\HttpUrl;

/**
 * @covers \Brera\KeyValue\KeyValueStoreKeyGenerator
 * @uses \Brera\Http\HttpUrl
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
		$stubProductId = $this->createProductId('');
		$key = $this->keyGenerator->createPoCProductHtmlKey($stubProductId);

		$this->assertInternalType('string',	$key);
	}

	/**
	 * @test
	 */
	public function itShouldGenerateTwoDifferentKeysForDifferentProductIds()
	{
		$productId1 = $this->createProductId('foo');
		$productId2 = $this->createProductId('bar');

		$key1 = $this->keyGenerator->createPoCProductHtmlKey($productId1);
		$key2 = $this->keyGenerator->createPoCProductHtmlKey($productId2);

		$this->assertFalse($key1 == $key2);
	}

	/**
	 * @test
	 */
	public function itShouldGenerateAStringAsPoCProductSeoUrlToIdKey()
	{
		$url = HttpUrl::fromString('http://example.com/path');
		$key = $this->keyGenerator->createPoCProductSeoUrlToIdKey($url);

		$this->assertInternalType('string', $key);
	}

	/**
	 * @test
	 */
	public function itShouldGenerateTwoDifferentKeysForPoCProductSeoUrlToIdKey()
	{
		$url1 = HttpUrl::fromString('http://example.com/path1');
		$url2 = HttpUrl::fromString('http://example.com/path2');

		$key1 = $this->keyGenerator->createPoCProductSeoUrlToIdKey($url1);
		$key2 = $this->keyGenerator->createPoCProductSeoUrlToIdKey($url2);

		$this->assertFalse($key1 == $key2);
	}

	/**
	 * @test
	 */
	public function itShouldNotGenerateKeysWithDirectorySeparator()
	{
		$url = HttpUrl::fromString('http://example.com/path');
		$key = $this->keyGenerator->createPoCProductSeoUrlToIdKey($url);

		$this->assertNotContains(DIRECTORY_SEPARATOR, $key);
	}

	/**
	 * @param string $id
	 * @return ProductId|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function createProductId($id)
	{
		$stubProductId = $this->getMockBuilder(ProductId::class)
			->disableOriginalConstructor()
			->getMock();

		$stubProductId->expects($this->any())
			->method('__toString')
			->willReturn($id);

		return $stubProductId;
	}
}
