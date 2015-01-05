<?php

namespace Brera\KeyValue;

class CredisKeyValueStoreIntegrationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var CredisKeyValueStore
	 */
	private $keyValueStore;

	protected function setUp()
	{
		$client = new \Credis_Client('localhost', '6379');
		$client->del('foo');
		$client->del('key1');
		$client->del('key2');

		$this->keyValueStore = new CredisKeyValueStore($client);
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

		$this->assertSame($values, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnFalseItKeyDoesNotExist()
	{
		$this->assertFalse($this->keyValueStore->has('foo'));
	}
}
