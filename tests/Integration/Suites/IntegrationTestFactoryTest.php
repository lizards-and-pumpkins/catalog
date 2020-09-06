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

    final protected function setUp(): void
    {
        $masterFactory = new CatalogMasterFactory();
        $this->factory = new IntegrationTestFactory();
        $masterFactory->register($this->factory);
        $masterFactory->register(new CommonFactory);
    }

    public function testInMemoryKeyValueStoreIsReturned(): void
    {
        $this->assertInstanceOf(InMemoryKeyValueStore::class, $this->factory->createKeyValueStore());
    }

    public function testInMemoryEventQueueIsReturned(): void
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createEventMessageQueue());
    }

    public function testInMemoryCommandQueueIsReturned(): void
    {
        $this->assertInstanceOf(InMemoryQueue::class, $this->factory->createCommandMessageQueue());
    }

    public function testInMemoryLoggerIsReturned(): void
    {
        $this->assertInstanceOf(InMemoryLogger::class, $this->factory->createLogger());
    }

    public function testInMemorySearchEngineIsReturned(): void
    {
        $this->assertInstanceOf(InMemorySearchEngine::class, $this->factory->createSearchEngine());
    }

    public function testItReturnsAnInMemoryUrlKeyStore(): void
    {
        $this->assertInstanceOf(InMemoryUrlKeyStore::class, $this->factory->createUrlKeyStore());
    }

    public function testLocalFilesystemStorageWriterIsReturned(): void
    {
        $this->assertInstanceOf(LocalFilesystemStorageWriter::class, $this->factory->createFileStorageWriter());
    }

    public function testLocalFilesystemStorageReaderIsReturned(): void
    {
        $this->assertInstanceOf(LocalFilesystemStorageReader::class, $this->factory->createFileStorageReader());
    }

    public function testArrayOfSearchableAttributeCodesIsReturned(): void
    {
        $result = $this->factory->getSearchableAttributeCodes();

        $this->assertIsArray($result);
        $this->assertContainsOnly('string', $result);
    }

    public function testImageProcessorCollectionIsReturned(): void
    {
        $this->assertInstanceOf(ImageProcessorCollection::class, $this->factory->createImageProcessorCollection());
    }

    public function testImageProcessorIsReturned(): void
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->createImageProcessor());
    }

    public function testItReturnsTheSameKeyValueStoreInstanceOnMultipleCalls(): void
    {
        $this->assertInstanceOf(KeyValueStore::class, $this->factory->getKeyValueStore());
        $this->assertSame($this->factory->getKeyValueStore(), $this->factory->getKeyValueStore());
    }

    public function testItReturnsTheSetKeyValueStore(): void
    {
        $stubKeyValueStore = $this->createMock(KeyValueStore::class);
        $this->factory->setKeyValueStore($stubKeyValueStore);

        $this->assertSame($stubKeyValueStore, $this->factory->getKeyValueStore());
    }

    public function testItReturnsTheSameEventQueueInstanceOnMultipleCalls(): void
    {
        $this->assertInstanceOf(DomainEventQueue::class, $this->factory->getEventQueue());
        $this->assertSame($this->factory->getEventQueue(), $this->factory->getEventQueue());
    }

    public function testItReturnsTheSameEventMessageQueueInstanceOnMultipleCalls(): void
    {
        $this->assertInstanceOf(Queue::class, $this->factory->getEventMessageQueue());
        $this->assertSame($this->factory->getEventMessageQueue(), $this->factory->getEventMessageQueue());
    }

    public function testItReturnsTheSetEventQueue(): void
    {
        $stubQueue = $this->createMock(Queue::class);
        $this->factory->setEventMessageQueue($stubQueue);
        $this->assertSame($stubQueue, $this->factory->getEventMessageQueue());
    }

    public function testItReturnsTheSameCommandQueueInstanceOnMultipleCalls(): void
    {
        $this->assertInstanceOf(CommandQueue::class, $this->factory->getCommandQueue());
        $this->assertSame($this->factory->getCommandQueue(), $this->factory->getCommandQueue());
    }

    public function testItReturnsTheSetCommandQueue(): void
    {
        $stubQueue = $this->createMock(Queue::class);
        $this->factory->setCommandMessageQueue($stubQueue);

        $this->assertSame($stubQueue, $this->factory->getCommandMessageQueue());
    }

    public function testItReturnsTheSameSearchEngineOnMultipleCalls(): void
    {
        $this->assertInstanceOf(SearchEngine::class, $this->factory->getSearchEngine());
        $this->assertSame($this->factory->getSearchEngine(), $this->factory->getSearchEngine());
    }

    public function testItReturnsTheSetSearchEngine(): void
    {
        $stubSearchEngine = $this->createMock(SearchEngine::class);
        $this->factory->setSearchEngine($stubSearchEngine);

        $this->assertSame($stubSearchEngine, $this->factory->getSearchEngine());
    }

    public function testItReturnsTheSameUrlKeyStoreOnMultipleCalls(): void
    {
        $this->assertInstanceOf(UrlKeyStore::class, $this->factory->getUrlKeyStore());
        $this->assertSame($this->factory->getUrlKeyStore(), $this->factory->getUrlKeyStore());
    }

    public function testItReturnsTheSetUrlKeyStore(): void
    {
        $stubUrlKeyStore = $this->createMock(UrlKeyStore::class);
        $this->factory->setUrlKeyStore($stubUrlKeyStore);

        $this->assertSame($stubUrlKeyStore, $this->factory->getUrlKeyStore());
    }

    public function testItReturnsAnExistingDirectoryAsTheFileStorageBasePathConfig(): void
    {
        $fileStorageBasePath = $this->factory->getFileStorageBasePathConfig();

        $this->assertIsString($fileStorageBasePath);
        $this->assertFileExists($fileStorageBasePath);
        $this->assertTrue(is_dir($fileStorageBasePath));
    }

    public function testItReturnsAnIntegrationTestTaxServiceLocator(): void
    {
        $this->assertInstanceOf(TaxServiceLocator::class, $this->factory->createTaxServiceLocator());
    }

    public function testProductViewLocatorIsReturned(): void
    {
        $this->assertInstanceOf(ProductViewLocator::class, $this->factory->createProductViewLocator());
    }

    public function testItReturnsAProductImageFileLocator(): void
    {
        $this->assertInstanceOf(ProductImageFileLocator::class, $this->factory->createProductImageFileLocator());
    }

    public function testItReturnsAnImageStorage(): void
    {
        $this->assertInstanceOf(ImageStorage::class, $this->factory->createImageStorage());
    }

    public function testReturnsMaxAllowedProductsPerSearchResultsPage(): void
    {
        $this->assertIsInt($this->factory->getMaxAllowedProductsPerSearchResultsPage());
    }

    public function testReturnsDefaultNumberOfProductsPerSearchResultsPage(): void
    {
        $this->assertIsInt($this->factory->getDefaultNumberOfProductsPerSearchResultsPage());
    }
}
