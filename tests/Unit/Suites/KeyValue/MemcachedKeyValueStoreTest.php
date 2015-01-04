<?php

namespace Brera\PoC\KeyValue;

/**
 * @covers  \Brera\PoC\KeyValue\MemcachedKeyValueStore
 */
class MemcachedKeyValueStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MemcachedKeyValueStore
     */
    private $store;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubClient;

    public function setUp()
    {
	    $this->stubClient = $this->getMockBuilder(\Memcached::class)
	        ->setMethods(['get', 'set', 'setMulti', 'getMulti', 'getResultCode'])
	        ->getMock();
        $this->store = new MemcachedKeyValueStore($this->stubClient);
    }

    /**
     * @test
     */
    public function itShouldSetAndGetAValue()
    {
        $key = 'key';
        $value = 'value';

	    $this->stubClient->expects($this->once())
		    ->method('set');
	    $this->stubClient->expects($this->any())
		    ->method('get')
		    ->willReturn($value);

        $this->store->set($key, $value);
        $this->assertEquals($value, $this->store->get($key));
    }

    /**
     * @test
     */
    public function itShouldReturnFalseItKeyDoesNotExist()
    {
	    $this->stubClient->expects($this->once())
		    ->method('getResultCode')
		    ->willReturn(MemcachedKeyValueStore::MEMCACHED_RES_NOTFOUND);

        $this->assertFalse($this->store->has('foo'));
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
	public function itShouldSetAndGetMultipleKeys()
	{
		$keys = ['key1', 'key2'];
		$values = ['foo', 'bar'];
		$items = array_combine($keys, $values);

		$this->stubClient->expects($this->once())
		                 ->method('setMulti');

		$this->store->multiSet($items);

		$this->stubClient->expects($this->once())
		                 ->method('getMulti')
		                 ->willReturn($values);

		$result = $this->store->multiGet($keys);

		$this->assertSame($values, $result);
	}
}
