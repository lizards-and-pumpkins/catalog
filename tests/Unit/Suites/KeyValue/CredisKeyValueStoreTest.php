<?php

namespace Brera\PoC\KeyValue;

use Credis_Client;

/**
 * @covers  \Brera\PoC\KeyValue\CRedisKeyValueStore
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
	        ->setMethods(array('get', 'set', 'exists'))
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
    public function itShouldNotHasBeforeSettingAValue()
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
     * @expectedException \Brera\PoC\KeyValue\KeyNotFoundException
     */
    public function itShouldThrowAnExceptionWhenValueIsNotSet()
    {
        $this->store->get('not set key');
    }
}
