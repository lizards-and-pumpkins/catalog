<?php

namespace Brera\Tests\Integration;

use Brera\DataPool\KeyValue\File\FileKeyValueStore;
use Brera\DataPool\SearchEngine\FileSearchEngine;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessorInstructionSequence;
use Brera\LocalFilesystemStorageReader;
use Brera\LocalFilesystemStorageWriter;
use Brera\PoCMasterFactory;
use Brera\SampleFactory;
use Brera\InMemoryLogger;
use Brera\Queue\InMemory\InMemoryQueue;

/**
 * @covers \Brera\SampleFactory
 * @uses   \Brera\FactoryTrait
 * @uses   \Brera\InMemoryLogger
 * @uses   \Brera\DataPool\KeyValue\File\FileKeyValueStore
 * @uses   \Brera\DataPool\SearchEngine\FileSearchEngine
 * @uses   \Brera\Queue\InMemory\InMemoryQueue
 * @uses   \Brera\Image\ImageMagickInscribeInstruction
 * @uses   \Brera\Image\ImageProcessor
 * @uses   \Brera\Image\ImageProcessorCollection
 * @uses   \Brera\Image\ImageProcessorInstructionSequence
 * @uses   \Brera\LocalFilesystemStorageReader
 * @uses   \Brera\LocalFilesystemStorageWriter
 * @uses   \Brera\MasterFactoryTrait
 */
class SampleFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleFactory
     */
    private $factory;

    public function setUp()
    {
        $masterFactory = new PoCMasterFactory();
        $this->factory = new SampleFactory();
        $masterFactory->register($this->factory);
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
    public function itShouldCreateAnInMemorySearchEngine()
    {
        $this->assertInstanceOf(FileSearchEngine::class, $this->factory->createSearchEngine());
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
    public function itShouldCreateSearchableAttributeCodesArray()
    {
        $this->assertInternalType('array', $this->factory->getSearchableAttributeCodes());
    }

    /**
     * @test
     */
    public function itShouldCreateImageProcessorCollection()
    {
        $this->assertInstanceOf(ImageProcessorCollection::class, $this->factory->createImageProcessorCollection());
    }

    /**
     * @test
     */
    public function itShouldCreateEnlargedImageProcessor()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getOriginalImageProcessor());
    }

    /**
     * @test
     */
    public function itShouldCreateOriginalImageFileStorageReader()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageReader::class,
            $this->factory->getOriginalImageFileStorageReader()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateOriginalImageFileStorageWriter()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageWriter::class,
            $this->factory->getOriginalImageFileStorageWriter()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateEnlargedImageProcessorInstructionSequence()
    {
        $this->assertInstanceOf(
            ImageProcessorInstructionSequence::class,
            $this->factory->getOriginalImageProcessorInstructionSequence()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateProductDetailsPageImageProcessor()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getProductDetailsPageImageProcessor());
    }

    /**
     * @test
     */
    public function itShouldCreateProductDetailsPageImageFileStorageReader()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageReader::class,
            $this->factory->getProductDetailsPageImageFileStorageReader()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateProductDetailsPageImageFileStorageWriter()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageWriter::class,
            $this->factory->getProductDetailsPageImageFileStorageWriter()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateProductDetailsPageImageProcessorInstructionSequence()
    {
        $this->assertInstanceOf(
            ImageProcessorInstructionSequence::class,
            $this->factory->getProductDetailsPageImageProcessorInstructionSequence()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateProductListingImageProcessor()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getProductListingImageProcessor());
    }

    /**
     * @test
     */
    public function itShouldCreateProductListingImageFileStorageReader()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageReader::class,
            $this->factory->getProductListingImageFileStorageReader()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateProductListingImageFileStorageWriter()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageWriter::class,
            $this->factory->getProductListingImageFileStorageWriter()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateProductListingImageProcessorInstructionSequence()
    {
        $this->assertInstanceOf(
            ImageProcessorInstructionSequence::class,
            $this->factory->getProductListingImageProcessorInstructionSequence()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateGalleyThumbnailImageProcessor()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getGalleyThumbnailImageProcessor());
    }

    /**
     * @test
     */
    public function itShouldCreateGalleyThumbnailImageFileStorageReader()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageReader::class,
            $this->factory->getGalleyThumbnailImageFileStorageReader()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateGalleyThumbnailImageFileStorageWriter()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageWriter::class,
            $this->factory->getGalleyThumbnailImageFileStorageWriter()
        );
    }

    /**
     * @test
     */
    public function itShouldCreateGalleyThumbnailImageProcessorInstructionSequence()
    {
        $this->assertInstanceOf(
            ImageProcessorInstructionSequence::class,
            $this->factory->getGalleyThumbnailImageProcessorInstructionSequence()
        );
    }
}
