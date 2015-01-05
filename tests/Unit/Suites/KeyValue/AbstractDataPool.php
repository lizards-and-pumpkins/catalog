<?php

namespace Brera\KeyValue;

use Brera\Product\ProductId;
use Brera\Http\HttpUrl;

class AbstractDataPool extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $stubKeyValueStore;

	/**
	 * @var KeyValueStoreKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $stubKeyGenerator;

	protected function setUp()
	{
		$this->stubKeyValueStore = $this->getMock(KeyValueStore::class);
		$this->stubKeyGenerator = $this->getMock(KeyValueStoreKeyGenerator::class);
	}

	protected function getStubProductId()
	{
		$productId = $this->getMockBuilder(ProductId::class)
		                  ->disableOriginalConstructor()
		                  ->getMock();
		return $productId;
	}

	protected function addSetMethodToStubKeyValueStore()
	{
		$this->stubKeyValueStore->expects($this->once())
		                        ->method('set');
	}

	protected function addGetMethodToStubKeyValueStore($returnValue)
	{
		$this->stubKeyValueStore->expects($this->once())
		                        ->method('get')
		                        ->willReturn($returnValue);
	}

	protected function addHasMethodToStubKeyValueStore($returnResult){
		$this->stubKeyValueStore->expects($this->once())
		                        ->method('has')
		                        ->willReturn($returnResult);
	}

	protected function addStubMethodToStubKeyGenerator($method)
	{
		$this->stubKeyGenerator->expects($this->once())
		                       ->method($method)
		                       ->willReturn('dummy_key');
	}

	protected function getDummyUrl()
	{
		$urlString = 'http://example.com/path';
		$url = HttpUrl::fromString($urlString);

		return $url;
	}
}
