<?php

namespace LizardsAndPumpkins\Tests\Integration;

use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\InMemorySearchEngine;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\InMemoryUrlKeyStore;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Image\ImageProcessor;
use LizardsAndPumpkins\Image\ImageProcessorCollection;
use LizardsAndPumpkins\Image\ImageProcessingStrategySequence;
use LizardsAndPumpkins\IntegrationTestFactory;
use LizardsAndPumpkins\Log\InMemoryLogger;
use LizardsAndPumpkins\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use LizardsAndPumpkins\LocalFilesystemStorageReader;
use LizardsAndPumpkins\LocalFilesystemStorageWriter;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\SampleMasterFactory;
use LizardsAndPumpkins\Queue\InMemory\InMemoryQueue;
use LizardsAndPumpkins\Utils\LocalFilesystem;

/**
 * @covers \LizardsAndPumpkins\IntegrationTestFactory
 * @uses   \LizardsAndPumpkins\DataPool\KeyValue\InMemory\InMemoryKeyValueStore
 * @uses   \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\Image\ImageMagickResizeStrategy
 * @uses   \LizardsAndPumpkins\Image\GdResizeStrategy
 * @uses   \LizardsAndPumpkins\Image\ImageProcessor
 * @uses   \LizardsAndPumpkins\Image\ImageProcessorCollection
 * @uses   \LizardsAndPumpkins\Image\ImageProcessingStrategySequence
 * @uses   \LizardsAndPumpkins\Log\InMemoryLogger
 * @uses   \LizardsAndPumpkins\LocalFilesystemStorageReader
 * @uses   \LizardsAndPumpkins\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Queue\InMemory\InMemoryQueue
 * @uses   \LizardsAndPumpkins\Utils\LocalFilesystem
 */
class IntegrationTestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationTestFactory
     */
    private $factory;

    public function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $this->factory = new IntegrationTestFactory();
        $masterFactory->register($this->factory);
    }

    public function testInMemoryKeyValueStoreIsReturned()
    {
        $this->assertInstanceOf(InMemoryKeyValueStore::class, $this->factory->createKeyValueStore());
    }

    public function testInMemoryEventQueueIsReturned()
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createEventQueue());
    }

    public function testInMemoryCommandQueueIsReturned()
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createCommandQueue());
    }

    public function testInMemoryLoggerIsReturned()
    {
        $this->assertInstanceOf(InMemoryLogger::class, $this->factory->createLogger());
    }

    public function testInMemorySearchEngineIsReturned()
    {
        $this->assertInstanceOf(InMemorySearchEngine::class, $this->factory->createSearchEngine());
    }

    public function testItReturnsAnInMemoryUrlKeyStore()
    {
        $this->assertInstanceOf(InMemoryUrlKeyStore::class, $this->factory->createUrlKeyStore());
    }

    public function testLocalFilesystemStorageWriterIsReturned()
    {
        $this->assertInstanceOf(LocalFilesystemStorageWriter::class, $this->factory->createImageFileStorageWriter());
    }

    public function testLocalFilesystemStorageReaderIsReturned()
    {
        $this->assertInstanceOf(LocalFilesystemStorageReader::class, $this->factory->createImageFileStorageReader());
    }

    public function testResizedImagesDirectoryIsCreated()
    {
        $resultImageDir = sys_get_temp_dir() . '/' . IntegrationTestFactory::PROCESSED_IMAGES_DIR;

        (new LocalFilesystem())->removeDirectoryAndItsContent($resultImageDir);

        $this->factory->createImageFileStorageWriter();

        $this->assertTrue(is_dir($resultImageDir));
    }

    public function testImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->createImageProcessingStrategySequence()
        );
    }

    public function testArrayOfSearchableAttributeCodesIsReturned()
    {
        $result = $this->factory->getSearchableAttributeCodes();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
    }

    public function testArrayOfProductListingFilterNavigationAttributeCodesIsReturned()
    {
        $result = $this->factory->getProductListingFilterNavigationAttributeCodes();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
    }

    public function testArrayOfProductSearchResultsFilterNavigationAttributeCodesIsReturned()
    {
        $result = $this->factory->getProductSearchResultsFilterNavigationAttributeCodes();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
    }

    public function testImageProcessorCollectionIsReturned()
    {
        $this->assertInstanceOf(ImageProcessorCollection::class, $this->factory->createImageProcessorCollection());
    }

    public function testImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->createImageProcessor());
    }

    public function testItReturnsTheSameKeyValueStoreInstanceOnMultipleCalls()
    {
        $this->assertInstanceOf(KeyValueStore::class, $this->factory->getKeyValueStore());
        $this->assertSame($this->factory->getKeyValueStore(), $this->factory->getKeyValueStore());
    }

    public function testItReturnsTheSetKeyValueStore()
    {
        $stubKeyValueStore = $this->getMock(KeyValueStore::class);
        $this->factory->setKeyValueStore($stubKeyValueStore);
        $this->assertSame($stubKeyValueStore, $this->factory->getKeyValueStore());
    }

    public function testItReturnsTheSameEventQueueInstanceOnMultipleCalls()
    {
        $this->assertInstanceOf(Queue::class, $this->factory->getEventQueue());
        $this->assertSame($this->factory->getEventQueue(), $this->factory->getEventQueue());
    }

    public function testItReturnsTheSetEventQueue()
    {
        $stubEventQueue = $this->getMock(Queue::class);
        $this->factory->setEventQueue($stubEventQueue);
        $this->assertSame($stubEventQueue, $this->factory->getEventQueue());
    }

    public function testItReturnsTheSameCommandQueueInstanceOnMultipleCalls()
    {
        $this->assertInstanceOf(Queue::class, $this->factory->getCommandQueue());
        $this->assertSame($this->factory->getCommandQueue(), $this->factory->getCommandQueue());
    }

    public function testItReturnsTheSetCommandQueue()
    {
        $stubCommandQueue = $this->getMock(Queue::class);
        $this->factory->setCommandQueue($stubCommandQueue);
        $this->assertSame($stubCommandQueue, $this->factory->getCommandQueue());
    }

    public function testItReturnsTheSameSearchEngineOnMultipleCalls()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->factory->getSearchEngine());
        $this->assertSame($this->factory->getSearchEngine(), $this->factory->getSearchEngine());
    }

    public function testItReturnsTheSetSearchEngine()
    {
        $stubSearchEngine = $this->getMock(SearchEngine::class);
        $this->factory->setSearchEngine($stubSearchEngine);
        $this->assertSame($stubSearchEngine, $this->factory->getSearchEngine());
    }

    public function testItReturnsTheSameUrlKeyStoreOnMultipleCalls()
    {
        $this->assertInstanceOf(UrlKeyStore::class, $this->factory->getUrlKeyStore());
        $this->assertSame($this->factory->getUrlKeyStore(), $this->factory->getUrlKeyStore());
    }

    public function testItReturnsTheSetUrlKeyStore()
    {
        $stubUrlKeyStore = $this->getMock(UrlKeyStore::class);
        $this->factory->setUrlKeyStore($stubUrlKeyStore);
        $this->assertSame($stubUrlKeyStore, $this->factory->getUrlKeyStore());
    }

    public function testItReturnsAnExistingDirectoryAsTheFileStorageBasePathConfig()
    {
        $fileStorageBasePath = $this->factory->getFileStorageBasePathConfig();
        $this->assertInternalType('string', $fileStorageBasePath);
        $this->assertFileExists($fileStorageBasePath);
        $this->assertTrue(is_dir($fileStorageBasePath));
    }
}
