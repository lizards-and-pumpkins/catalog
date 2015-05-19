<?php

namespace Brera\Tests\Integration;

use Brera\DataPool\KeyValue\File\FileKeyValueStore;
use Brera\DataPool\SearchEngine\FileSearchEngine;
use Brera\Image\ImageProcessor;
use Brera\Image\ImageProcessorCollection;
use Brera\Image\ImageProcessorCommandSequence;
use Brera\LocalImage;
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
 * @uses   \Brera\Image\ImageMagickInscribeCommand
 * @uses   \Brera\Image\ImageProcessor
 * @uses   \Brera\Image\ImageProcessorCollection
 * @uses   \Brera\Image\ImageProcessorCommandSequence
 * @uses   \Brera\LocalImage
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
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->getEnlargedImageProcessor());
    }

    /**
     * @test
     */
    public function itShouldCreateEnlargedImageFileStorage()
    {
        $this->assertInstanceOf(LocalImage::class, $this->factory->getEnlargedImageFileStorage());
    }

    /**
     * @test
     */
    public function itShouldCreateEnlargedImageProcessorCommandSequence()
    {
        $this->assertInstanceOf(
            ImageProcessorCommandSequence::class,
            $this->factory->getEnlargedImageProcessorCommandSequence()
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
    public function itShouldCreateProductDetailsPageImageFileStorage()
    {
        $this->assertInstanceOf(LocalImage::class, $this->factory->getProductDetailsPageImageFileStorage());
    }

    /**
     * @test
     */
    public function itShouldCreateProductDetailsPageImageProcessorCommandSequence()
    {
        $this->assertInstanceOf(
            ImageProcessorCommandSequence::class,
            $this->factory->getProductDetailsPageImageProcessorCommandSequence()
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
    public function itShouldCreateProductListingImageFileStorage()
    {
        $this->assertInstanceOf(LocalImage::class, $this->factory->getProductListingImageFileStorage());
    }

    /**
     * @test
     */
    public function itShouldCreateProductListingImageProcessorCommandSequence()
    {
        $this->assertInstanceOf(
            ImageProcessorCommandSequence::class,
            $this->factory->getProductListingImageProcessorCommandSequence()
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
    public function itShouldCreateGalleyThumbnailImageFileStorage()
    {
        $this->assertInstanceOf(LocalImage::class, $this->factory->getGalleyThumbnailImageFileStorage());
    }

    /**
     * @test
     */
    public function itShouldCreateGalleyThumbnailImageProcessorCommandSequence()
    {
        $this->assertInstanceOf(
            ImageProcessorCommandSequence::class,
            $this->factory->getGalleyThumbnailImageProcessorCommandSequence()
        );
    }
}
