<?php

namespace Brera\PoC\KeyValue;

class MemcachedKeyValueStoreIntegrationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var MemcachedKeyValueStore
	 */
	private $keyValueStore;

	protected function setUp()
	{
		$client = new \Memcached();
		$client->addServer('localhost', 11211);
		$client->deleteMulti(['foo', 'key1', 'key2']);

		$this->keyValueStore = new MemcachedKeyValueStore($client);
	}

	/**
	 * @test
	 */
	public function itShouldSetAndGetAValue()
	{
		$this->keyValueStore->set('foo', 'bar');
		$result = $this->keyValueStore->get('foo');

		$this->assertEquals('bar', $result);
	}

	/**
	 * @test
	 */
	public function itShouldSetAndGetMultipleValues()
	{
		$keys = ['key1', 'key2'];
		$values = ['foo', 'bar'];
		$items = array_combine($keys, $values);

		$this->keyValueStore->multiSet($items);
		$result = $this->keyValueStore->multiGet($keys);

		$this->assertSame($items, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnFalseItKeyDoesNotExist()
	{
		$this->assertFalse($this->keyValueStore->has('foo'));
	}
}
