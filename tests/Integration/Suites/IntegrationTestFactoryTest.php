<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\KeyValueStore\InMemoryKeyValueStore;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\InMemorySearchEngine;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\InMemoryUrlKeyStore;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessor;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Logging\InMemoryLogger;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;
use LizardsAndPumpkins\Import\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Import\ImageStorage\ImageStorage;
use LizardsAndPumpkins\Messaging\Queue\InMemoryQueue;
use LizardsAndPumpkins\Messaging\Queue\Queue;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageReader;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageWriter;
use PHPUnit\Framework\TestCase;

class IntegrationTestFactoryTest extends TestCase
{
    /**
     * @var IntegrationTestFactory
     */
    private $factory;

    public function setUp()
    {
        $masterFactory = new CatalogMasterFactory();
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
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createEventMessageQueue());
    }

    public function testInMemoryCommandQueueIsReturned()
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createCommandMessageQueue());
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

    public function testArrayOfSearchableAttributeCodesIsReturned()
    {
        $result = $this->factory->getSearchableAttributeCodes();

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
        /** @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject $stubKeyValueStore */
        $stubKeyValueStore = $this->createMock(KeyValueStore::class);
        $this->factory->setKeyValueStore($stubKeyValueStore);
        $this->assertSame($stubKeyValueStore, $this->factory->getKeyValueStore());
    }

    public function testItReturnsTheSameEventQueueInstanceOnMultipleCalls()
    {
        $this->assertInstanceOf(DomainEventQueue::class, $this->factory->getEventQueue());
        $this->assertSame($this->factory->getEventQueue(), $this->factory->getEventQueue());
    }

    public function testItReturnsTheSameEventMessageQueueInstanceOnMultipleCalls()
    {
        $this->assertInstanceOf(Queue::class, $this->factory->getEventMessageQueue());
        $this->assertSame($this->factory->getEventMessageQueue(), $this->factory->getEventMessageQueue());
    }

    public function testItReturnsTheSetEventQueue()
    {
        /** @var Queue|\PHPUnit_Framework_MockObject_MockObject $stubQueue */
        $stubQueue = $this->createMock(Queue::class);
        $this->factory->setEventMessageQueue($stubQueue);
        $this->assertSame($stubQueue, $this->factory->getEventMessageQueue());
    }

    public function testItReturnsTheSameCommandQueueInstanceOnMultipleCalls()
    {
        $this->assertInstanceOf(CommandQueue::class, $this->factory->getCommandQueue());
        $this->assertSame($this->factory->getCommandQueue(), $this->factory->getCommandQueue());
    }

    public function testItReturnsTheSetCommandQueue()
    {
        /** @var Queue|\PHPUnit_Framework_MockObject_MockObject $stubQueue */
        $stubQueue = $this->createMock(Queue::class);
        $this->factory->setCommandMessageQueue($stubQueue);
        $this->assertSame($stubQueue, $this->factory->getCommandMessageQueue());
    }

    public function testItReturnsTheSameSearchEngineOnMultipleCalls()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->factory->getSearchEngine());
        $this->assertSame($this->factory->getSearchEngine(), $this->factory->getSearchEngine());
    }

    public function testItReturnsTheSetSearchEngine()
    {
        /** @var SearchEngine|\PHPUnit_Framework_MockObject_MockObject $stubSearchEngine */
        $stubSearchEngine = $this->createMock(SearchEngine::class);
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
        $stubUrlKeyStore = $this->createMock(UrlKeyStore::class);
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

    public function testItReturnsAnIntegrationTestTaxServiceLocator()
    {
        $this->assertInstanceOf(TaxServiceLocator::class, $this->factory->createTaxServiceLocator());
    }

    public function testProductViewLocatorIsReturned()
    {
        $this->assertInstanceOf(ProductViewLocator::class, $this->factory->createProductViewLocator());
    }

    public function testItReturnsAProductImageFileLocator()
    {
        $this->assertInstanceOf(ProductImageFileLocator::class, $this->factory->createProductImageFileLocator());
    }

    public function testItReturnsAnImageStorage()
    {
        $this->assertInstanceOf(ImageStorage::class, $this->factory->createImageStorage());
    }

    public function testReturnsMaxAllowedProductsPerSearchResultsPage()
    {
        $this->assertInternalType('int', $this->factory->getMaxAllowedProductsPerSearchResultsPage());
    }

    public function testReturnsDefaultNumberOfProductsPerSearchResultsPage()
    {
        $this->assertInternalType('int', $this->factory->getDefaultNumberOfProductsPerSearchResultsPage());
    }
}
