<?php


namespace Brera\Tests\Integration;

use Brera\KeyValue\File\FileKeyValueStore;
use Brera\SampleFactory;
use Brera\InMemoryLogger;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\SearchEngine\InMemorySearchEngine;

/**
 * @covers \Brera\SampleFactory
 * @uses   \Brera\InMemoryLogger
 * @uses   \Brera\KeyValue\File\FileKeyValueStore
 * @uses   \Brera\Queue\InMemory\InMemoryQueue
 */
class SampleFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new SampleFactory();
    }

    /**
     * @test
     */
    public function itShouldCreateAFileKeyValueStore()
    {
        $this->assertInstanceOf(FileKeyValueStore::class, $this->factory->createKeyValueStore());
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

    /**
     * @test
     */
    public function itShouldCreateAnInMemorySearchEngine()
    {
        $this->assertInstanceOf(InMemorySearchEngine::class, $this->factory->createSearchEngine());
    }
}
