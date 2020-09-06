<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyValueStore;

use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\Util\Storage\Clearable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\KeyValueStore\InMemoryKeyValueStore
 */
class InMemoryKeyValueStoreTest extends TestCase
{
    /**
     * @var InMemoryKeyValueStore
     */
    private $store;

    public function setUp(): void
    {
        $this->store = new InMemoryKeyValueStore;
    }

    public function testValueIsSetAndRetrieved(): void
    {
        $key = 'key';
        $value = 'value';

        $this->store->set($key, $value);
        $this->assertEquals($value, $this->store->get($key));
    }

    public function testTrueIsReturnedOnlyAfterValueIsSet(): void
    {
        $key = 'key';
        $value = 'value';

        $this->assertFalse($this->store->has($key));

        $this->store->set($key, $value);
        $this->assertTrue($this->store->has($key));
    }

    public function testExceptionIsThrownIfValueIsNotSet(): void
    {
        $this->expectException(KeyNotFoundException::class);
        $this->store->get('not set key');
    }

    public function testMultipleKeysAreSetAndRetrieved(): void
    {
        $keys = ['key1', 'key2'];
        $values = ['foo', 'bar'];
        $items = array_combine($keys, $values);

        $this->store->multiSet($items);
        $result = $this->store->multiGet(...$keys);

        $this->assertSame($items, $result);
    }

    public function testClearableInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(Clearable::class, $this->store);
    }

    public function testStorageContentIsFlushed(): void
    {
        $key = 'key';
        $value = 'value';

        $this->store->set($key, $value);
        $this->store->clear();
        $this->assertFalse($this->store->has($key));
    }
}
