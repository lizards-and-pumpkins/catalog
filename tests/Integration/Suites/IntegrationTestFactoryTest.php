<?php

namespace LizardsAndPumpkins\Tests\Integration;

use LizardsAndPumpkins\CommonFactory;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
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

class IntegrationTestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationTestFactory
     */
    private $factory;

    /**
     * @param array[] $filterRanges
     */
    private function assertFilterRangesFormat(array $filterRanges)
    {
        array_map(function (array $filterRanges) {
            $this->assertInternalType('array', $filterRanges);
            $this->assertContainsOnly('array', $filterRanges);
            array_map(function (array $range) {
                $this->assertCount(2, $range);
                $this->assertArrayHasKey('from', $range);
                $this->assertArrayHasKey('to', $range);
            }, $filterRanges);
        }, $filterRanges);
    }

    public function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $this->factory = new IntegrationTestFactory();
        $masterFactory->register($this->factory);
        $masterFactory->register(new CommonFactory);
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
        $this->assertInstanceOf(LocalFilesystemStorageWriter::class, $this->factory->createFileStorageWriter());
    }

    public function testLocalFilesystemStorageReaderIsReturned()
    {
        $this->assertInstanceOf(LocalFilesystemStorageReader::class, $this->factory->createFileStorageReader());
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
        $result = $this->factory->getProductListingFilterNavigationConfig();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('array', $result);
        $this->assertFilterRangesFormat($result);
    }

    public function testArrayOfProductSearchResultsFilterNavigationAttributeCodesIsReturned()
    {
        $result = $this->factory->getProductSearchResultsFilterNavigationConfig();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('array', $result);
        $this->assertFilterRangesFormat($result);
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
        /** @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject $stubKeyValueStore */
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
        /** @var Queue|\PHPUnit_Framework_MockObject_MockObject $stubEventQueue */
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
        /** @var Queue|\PHPUnit_Framework_MockObject_MockObject $stubCommandQueue */
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
        /** @var SearchEngine|\PHPUnit_Framework_MockObject_MockObject $stubSearchEngine */
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
        /** @var UrlKeyStore|\PHPUnit_Framework_MockObject_MockObject $stubUrlKeyStore */
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

    public function testSameInstanceOfProductListingSortOrderConfigIsReturnedOnMultipleCalls()
    {
        $this->assertContainsOnly(SortOrderConfig::class, $this->factory->getProductListingSortOrderConfig());
        $this->assertSame(
            $this->factory->getProductListingSortOrderConfig(),
            $this->factory->getProductListingSortOrderConfig()
        );
    }

    public function testSameInstanceOfProductSearchSortOrderConfigIsReturnedOnMultipleCalls()
    {
        $this->assertContainsOnly(SortOrderConfig::class, $this->factory->getProductSearchSortOrderConfig());
        $this->assertSame(
            $this->factory->getProductSearchSortOrderConfig(),
            $this->factory->getProductSearchSortOrderConfig()
        );
    }

    public function testSameInstanceOfProductSearchAutosuggestionSortOrderConfigIsReturnedOnMultipleCalls()
    {
        $this->assertInstanceOf(
            SortOrderConfig::class,
            $this->factory->getProductSearchAutosuggestionSortOrderConfig()
        );
        $this->assertSame(
            $this->factory->getProductSearchAutosuggestionSortOrderConfig(),
            $this->factory->getProductSearchAutosuggestionSortOrderConfig()
        );
    }
}
