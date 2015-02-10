<?php


namespace Brera\Tests\Integration;

use Brera\IntegrationTestFactory;
use Brera\InMemoryLogger;
use Brera\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\Queue\InMemory\InMemoryQueue;

/**
 * @covers \Brera\IntegrationTestFactory
 * @uses   \Brera\InMemoryLogger
 * @uses   \Brera\KeyValue\InMemory\InMemoryKeyValueStore
 * @uses   \Brera\Queue\InMemory\InMemoryQueue
 */
class IntegrationTestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationTestFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new IntegrationTestFactory();
    }

    /**
     * @test
     */
    public function itShouldCreateAnInMemoryKeyValueStore()
    {
        $this->assertInstanceOf(InMemoryKeyValueStore::class, $this->factory->createKeyValueStore());
    }

    /**
     * @test
     */
    public function itShouldCreateAnInMemoryEventQueue()
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createEventQueue());
    }

    /**
     * @test
     */
    public function itShouldCreateAnInMemoryLogger()
    {
        $this->assertInstanceOf(InMemoryLogger::class, $this->factory->createLogger());
    }
}
