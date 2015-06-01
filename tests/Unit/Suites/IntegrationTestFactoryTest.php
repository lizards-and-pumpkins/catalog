<?php


namespace Brera\Tests\Integration;

use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessorInstructionSequence;
use Brera\IntegrationTestFactory;
use Brera\InMemoryLogger;
use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\LocalFilesystemStorageReader;
use Brera\LocalFilesystemStorageWriter;
use Brera\PoCMasterFactory;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\Utils\LocalFilesystem;

/**
 * @covers \Brera\IntegrationTestFactory
 * @uses   \Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore
 * @uses   \Brera\FactoryTrait
 * @uses   \Brera\Image\ImageMagickResizeInstruction
 * @uses   \Brera\Image\ImageProcessor
 * @uses   \Brera\Image\ImageProcessorCollection
 * @uses   \Brera\Image\ImageProcessorInstructionSequence
 * @uses   \Brera\InMemoryLogger
 * @uses   \Brera\LocalFilesystemStorageReader
 * @uses   \Brera\LocalFilesystemStorageWriter
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\Queue\InMemory\InMemoryQueue
 * @uses   \Brera\Utils\LocalFilesystem
 */
class IntegrationTestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationTestFactory
     */
    private $factory;

    public function setUp()
    {
        $masterFactory = new PoCMasterFactory();
        $this->factory = new IntegrationTestFactory();
        $masterFactory->register($this->factory);
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
    public function itShouldCreateLocalFilesystemStorageWriter()
    {
        $this->assertInstanceOf(LocalFilesystemStorageWriter::class, $this->factory->getImageFileStorageWriter());
    }

    /**
     * @test
     */
    public function itShouldCreateLocalFilesystemStorageReader()
    {
        $this->assertInstanceOf(LocalFilesystemStorageReader::class, $this->factory->getImageFileStorageReader());
    }

    /**
     * @test
     */
    public function itShouldCreateResizedImagesDirectoryIfItDoesNotExist()
    {
        $resultImageDir = sys_get_temp_dir() . '/' . IntegrationTestFactory::PROCESSED_IMAGES_DIR;

        (new LocalFilesystem())->removeDirectoryAndItsContent($resultImageDir);

        $this->factory->getImageFileStorageWriter();

        $this->assertTrue(is_dir($resultImageDir));
    }

    /**
     * @test
     */
    public function itShouldReturnImageProcessorInstructionSequence()
    {
        $this->assertInstanceOf(
            ImageProcessorInstructionSequence::class,
            $this->factory->getImageProcessorInstructionSequence()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateSearchableAttributeCodesArray()
    {
        $this->assertInternalType('array', $this->factory->getSearchableAttributeCodes());
    }

    /**
     * @test
     */
    public function itShouldReturnImageProcessorCollection()
    {
        $this->assertInstanceOf(ImageProcessorCollection::class, $this->factory->createImageProcessorCollection());
    }

    /**
     * @test
     */
    public function itShouldReturnImageProcessor()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getImageProcessor());
    }
}
