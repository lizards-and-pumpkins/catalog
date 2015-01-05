<?php

namespace Brera\KeyValue;

use Credis_Client;

/**
 * @covers  \Brera\KeyValue\CRedisKeyValueStore
 */
class CredisKeyValueStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CredisKeyValueStore
     */
    private $store;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubClient;

    public function setUp()
    {
	    $this->stubClient = $this->getMockBuilder(Credis_Client::class)
	        ->setMethods(['get', 'set', 'exists', 'mGet', 'mSet'])
	        ->getMock();
        $this->store = new CredisKeyValueStore($this->stubClient);
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
    public function itShouldReturnTrueOnlyAfterValueIsSet()
    {
        $key = 'key';
        $value = 'value';

        $this->assertFalse($this->store->has($key));

	    $this->stubClient->expects($this->once())
		    ->method('exists')
		    ->willReturn(true);

        $this->store->set($key, $value);
        $this->assertTrue($this->store->has($key));
    }

    /**
     * @test
     * @expectedException \Brera\KeyValue\KeyNotFoundException
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
		                 ->method('mSet');

		$this->store->multiSet($items);

		$this->stubClient->expects($this->once())
		                 ->method('mGet')
		                 ->willReturn($values);

		$result = $this->store->multiGet($keys);

		$this->assertSame($values, $result);
	}
}
