<?php


namespace Brera\Tests\Integration;

use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\Image\ImageProcessorCommandSequence;
use Brera\IntegrationTestFactory;
use Brera\InMemoryLogger;
use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\LocalImage;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\Utils\LocalFilesystem;

/**
 * @covers \Brera\IntegrationTestFactory
 * @uses   \Brera\Image\ImageMagickResizeCommand
 * @uses   \Brera\Image\ImageProcessorCommandSequence
 * @uses   \Brera\InMemoryLogger
 * @uses   \Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore
 * @uses   \Brera\Utils\LocalFilesystem
 * @uses   \Brera\LocalImage
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

    /**
     * @test
     */
    public function itShouldCreateAnInMemorySearchEngine()
    {
        $this->assertInstanceOf(InMemorySearchEngine::class, $this->factory->createSearchEngine());
    }

    /**
     * @test
     */
    public function itShouldCreateStaticFileStorage()
    {
        $this->assertInstanceOf(LocalImage::class, $this->factory->createFileStorage());
    }

    /**
     * @test
     */
    public function itShouldCreateResizedImagesDirectoryIfItDoesNotExist()
    {
        $resultImageDir = sys_get_temp_dir() . '/' . IntegrationTestFactory::PROCESSED_IMAGES_DIR;

        (new LocalFilesystem())->removeDirectoryAndItsContent($resultImageDir);

        $this->factory->createFileStorage();

        $this->assertTrue(is_dir($resultImageDir));
    }

    /**
     * @test
     */
    public function itShouldReturnImageProcessorCommandSequence()
    {
        $this->assertInstanceOf(
            ImageProcessorCommandSequence::class,
            $this->factory->createImageProcessorCommandSequence()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateSearchableAttributeCodesArray()
    {
        $this->assertInternalType('array', $this->factory->getSearchableAttributeCodes());
    }
}
