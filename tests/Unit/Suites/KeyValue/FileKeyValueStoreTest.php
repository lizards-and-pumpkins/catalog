<?php

namespace Brera\PoC\KeyValue;

/**
 * @covers  \Brera\PoC\KeyValue\FileKeyValueStore
 */
class FileKeyValueStoreTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var FileKeyValueStore
	 */
	private $store;

	protected function setUp()
	{
		array_map('unlink', glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'key_*'));

		$this->store = new FileKeyValueStore();
	}

	/**
	 * @test
	 * @expectedException \Brera\PoC\KeyValue\KeyValueStoreNotAvailableException
	 */
	public function itShouldThrowAnExceptionIfStorageDirIsNotWritable()
	{
		new FileKeyValueStore('foo');
	}

	/**
	 * @test
	 */
	public function itShouldSetAndGetAValue()
	{
		$key = 'key';
		$value = 'value';

		$this->store->set($key, $value);
		$this->assertEquals($value, $this->store->get($key));
	}

	/**
	 * @test
	 * @expectedException \Brera\PoC\KeyValue\KeyNotFoundException
	 */
	public function itShouldThrowAnExceptionWhenValueIsNotSet()
	{
		$this->store->get('not set key');
	}

	/**
	 * @test
	 */
	public function itShouldReturnTrueOnlyAfterValueIsSet()
	{
		$key = 'key';
		$value = 'value';

		$this->assertFalse($this->store->has($key));

		$this->store->set($key, $value);
		$this->assertTrue($this->store->has($key));
	}


	/**
	 * @test
	 */
	public function itShouldSetAndGetMultipleKeys()
	{
		$keys = ['key1', 'key2'];
		$values = ['foo', 'bar'];
		$items = array_combine($keys, $values);

		$this->store->multiSet($items);
		$result = $this->store->multiGet($keys);

		$this->assertSame($values, $result);
	}
}
