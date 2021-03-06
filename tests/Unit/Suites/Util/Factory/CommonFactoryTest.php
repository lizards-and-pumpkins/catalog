<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\DataVersion\ContextVersion;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataVersion\CurrentDataVersionWasSetDomainEventHandler;
use LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommandHandler;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SnippetReader;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEventHandler;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetRenderer;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage;
use LizardsAndPumpkins\Import\Image\AddImageCommandHandler;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Import\ImageStorage\MediaBaseUrlBuilder;
use LizardsAndPumpkins\Import\ImportCatalogCommandHandler;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator;
use LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator;
use LizardsAndPumpkins\Import\Product\ProductImportCommandLocator;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductProjector;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\Product\QueueImportCommands;
use LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommandHandler;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerLocator;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandlerLocator;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Queue;
use LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\Import\ProductDetailTemplateSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailMetaSnippetRenderer;
use LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler;
use LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder;
use LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator;
use LizardsAndPumpkins\ProductSearch\Import\ConfigurableProductAttributeValueCollector;
use LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Core\Factory\Exception\NoMasterFactorySetException;
use LizardsAndPumpkins\Core\Factory\Exception\UndefinedFactoryMethodException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Core\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommandHandler
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Messaging\Command\CommandConsumer
 * @uses   \LizardsAndPumpkins\Messaging\Command\CommandHandlerLocator
 * @uses   \LizardsAndPumpkins\Messaging\Event\DomainEventConsumer
 * @uses   \LizardsAndPumpkins\Messaging\Event\DomainEventHandlerLocator
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Price\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\ProductProjector
 * @uses   \LizardsAndPumpkins\ProductDetail\Import\ProductDetailTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\GenericSnippetProjector
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingProjector
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\ProductDetail\TemplateRendering\ProductDetailViewBlockRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\DataPool\SnippetReader
 * @uses   \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessor
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategySequence
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageReader
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Import\Product\QueueImportCommands
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator
 * @uses   \LizardsAndPumpkins\Translation\CsvTranslator
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\MediaDirectoryBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommand
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommand
 * @uses   \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommand
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListing
 * @uses   \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler
 * @uses   \LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope
 * @uses   \LizardsAndPumpkins\Import\ImportCatalogCommand
 * @uses   \LizardsAndPumpkins\Import\ImportCatalogCommandHandler
 * @uses   \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEvent
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommandHandler
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\CurrentDataVersionWasSetDomainEvent
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\CurrentDataVersionWasSetDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommandHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand
 */
class CommonFactoryTest extends TestCase
{
    /**
     * @var CommonFactory
     */
    private $commonFactory;

    final protected function setUp(): void
    {
        $masterFactory = new CatalogMasterFactory();
        $masterFactory->register(new UnitTestFactory($this));
        $this->commonFactory = new CommonFactory();
        $masterFactory->register($this->commonFactory);
    }

    public function testExceptionIsThrownIfNoMasterFactoryIsSet(): void
    {
        $this->expectException(NoMasterFactorySetException::class);
        (new CommonFactory())->createDomainEventConsumer();
    }

    public function testProductWasUpdatedDomainEventHandlerIsReturned(): void
    {
        $result = $this->commonFactory->createProductWasUpdatedDomainEventHandler();
        $this->assertInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testTemplateWasUpdatedDomainEventHandlerIsReturned(): void
    {
        $result = $this->commonFactory->createTemplateWasUpdatedDomainEventHandler();
        $this->assertInstanceOf(TemplateWasUpdatedDomainEventHandler::class, $result);
    }

    public function testProductListingWasAddedDomainEventHandlerIsReturned(): void
    {
        $result = $this->commonFactory->createProductListingWasAddedDomainEventHandler();
        $this->assertInstanceOf(ProductListingWasAddedDomainEventHandler::class, $result);
    }

    public function testProductProjectorIsReturned(): void
    {
        $result = $this->commonFactory->createProductProjector();
        $this->assertInstanceOf(ProductProjector::class, $result);
    }

    public function testProductDetailMetaSnippetKeyGeneratorIsReturned(): void
    {
        $result = $this->commonFactory->createProductDetailPageMetaSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testProductXmlToProductBuilderLocatorIsReturned(): void
    {
        $result = $this->commonFactory->createProductXmlToProductBuilderLocator();
        $this->assertInstanceOf(ProductXmlToProductBuilderLocator::class, $result);
    }

    public function testProductXmlToProductBuilderLocatorProxyFactoryIsReturned(): void
    {
        $proxy = $this->commonFactory->createProductXmlToProductBuilderLocatorProxyFactoryMethod();
        $this->assertInstanceOf(ProductXmlToProductBuilderLocator::class, $proxy());
    }

    public function testProductListingBuilderIsReturned(): void
    {
        $result = $this->commonFactory->createProductListingBuilder();
        $this->assertInstanceOf(ProductListingBuilder::class, $result);
    }

    public function testContextBuilderIsReturned(): void
    {
        $result = $this->commonFactory->createContextBuilder();
        $this->assertInstanceOf(ContextBuilder::class, $result);
    }

    public function testDomainEventHandlerLocatorIsReturned(): void
    {
        $result = $this->commonFactory->createDomainEventHandlerLocator();
        $this->assertInstanceOf(DomainEventHandlerLocator::class, $result);
    }

    public function testDataPoolWriterIsReturned(): void
    {
        $result = $this->commonFactory->createDomainEventHandlerLocator();
        $this->assertInstanceOf(DomainEventHandlerLocator::class, $result);
    }

    public function testDomainEventConsumerIsReturned(): void
    {
        $result = $this->commonFactory->createDomainEventConsumer();
        $this->assertInstanceOf(DomainEventConsumer::class, $result);
    }

    public function testDomainEventQueueIsReturned(): void
    {
        $result = $this->commonFactory->getEventQueue();
        $this->assertInstanceOf(DomainEventQueue::class, $result);
    }

    public function testSameDomainEventQueueInstanceIsReturned(): void
    {
        $result1 = $this->commonFactory->getEventQueue();
        $result2 = $this->commonFactory->getEventQueue();
        $this->assertSame($result1, $result2);
    }

    public function testDomainEventMessageQueueIsReturned(): void
    {
        $result = $this->commonFactory->getEventMessageQueue();
        $this->assertInstanceOf(Queue::class, $result);
    }

    public function testSameDomainEventMessageQueueInstanceIsReturned(): void
    {
        $result1 = $this->commonFactory->getEventMessageQueue();
        $result2 = $this->commonFactory->getEventMessageQueue();
        $this->assertSame($result1, $result2);
    }

    public function testDataPoolReaderIsReturned(): void
    {
        $result = $this->commonFactory->createDataPoolReader();
        $this->assertInstanceOf(DataPoolReader::class, $result);
    }

    public function testExceptionWithHelpfulMessageIsThrownIfNoKeyValueStoreFactoryIsRegistered(): void
    {
        $masterFactory = new CatalogMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $this->expectException(UndefinedFactoryMethodException::class);
        $this->expectExceptionMessage('Unable to create KeyValueStore. Is the factory registered?');

        $commonFactory->createDataPoolReader();
    }

    public function testExceptionWithHelpfulMessageIsThrownIfNoEventQueueFactoryIsRegistered(): void
    {
        $masterFactory = new CatalogMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $this->expectException(UndefinedFactoryMethodException::class);
        $this->expectExceptionMessage('Unable to create EventQueue. Is the factory registered?');

        $commonFactory->getEventQueue();
    }

    public function testExceptionWithHelpfulMessageIsThrownIfNoLoggerFactoryIsRegistered(): void
    {
        $masterFactory = new CatalogMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $this->expectException(UndefinedFactoryMethodException::class);
        $this->expectExceptionMessage('Unable to create Logger. Is the factory registered?');

        $commonFactory->getLogger();
    }

    public function testLoggerInstanceIsReturned(): void
    {
        $resultA = $this->commonFactory->getLogger();
        $resultB = $this->commonFactory->getLogger();
        $this->assertInstanceOf(Logger::class, $resultA);
        $this->assertSame($resultA, $resultB);
    }

    public function testImageImportEventDomainHandlerIsReturned(): void
    {
        $result = $this->commonFactory->createImageWasAddedDomainEventHandler();
        $this->assertInstanceOf(ImageWasAddedDomainEventHandler::class, $result);
    }

    public function testCommandConsumerIsReturned(): void
    {
        $result = $this->commonFactory->createCommandConsumer();
        $this->assertInstanceOf(CommandConsumer::class, $result);
    }

    public function testCommandQueueIsReturned(): void
    {
        $result = $this->commonFactory->getCommandQueue();
        $this->assertInstanceOf(CommandQueue::class, $result);
    }

    public function testSameCommandQueueInstanceIsReturned(): void
    {
        $result1 = $this->commonFactory->getCommandQueue();
        $result2 = $this->commonFactory->getCommandQueue();

        $this->assertSame($result1, $result2);
    }

    public function testReturnsCommandMessageQueue(): void
    {
        $result = $this->commonFactory->getCommandMessageQueue();
        $this->assertInstanceOf(Queue::class, $result);
    }

    public function testReturnsSameCommandMessageQueueInstance(): void
    {
        $result1 = $this->commonFactory->getCommandMessageQueue();
        $result2 = $this->commonFactory->getCommandMessageQueue();

        $this->assertSame($result1, $result2);
    }

    public function testCommandHandlerLocatorIsReturned(): void
    {
        $result = $this->commonFactory->createCommandHandlerLocator();
        $this->assertInstanceOf(CommandHandlerLocator::class, $result);
    }

    public function testUpdateContentBlockCommandHandlerIsReturned(): void
    {
        $result = $this->commonFactory->createUpdateContentBlockCommandHandler();
        $this->assertInstanceOf(UpdateContentBlockCommandHandler::class, $result);
    }

    public function testReturnsAnUpdateTemplateCommandHandler(): void
    {
        $result = $this->commonFactory->createUpdateTemplateCommandHandler();
        $this->assertInstanceOf(UpdateTemplateCommandHandler::class, $result);
    }

    public function testContentBlockWasUpdatedDomainEventHandlerIsReturned(): void
    {
        $result = $this->commonFactory->createContentBlockWasUpdatedDomainEventHandler();
        $this->assertInstanceOf(ContentBlockWasUpdatedDomainEventHandler::class, $result);
    }

    public function testUpdateProductCommandHandlerIsReturned(): void
    {
        $result = $this->commonFactory->createUpdateProductCommandHandler();
        $this->assertInstanceOf(UpdateProductCommandHandler::class, $result);
    }

    public function testAddProductListingCommandHandlerIsReturned(): void
    {
        $result = $this->commonFactory->createAddProductListingCommandHandler();
        $this->assertInstanceOf(AddProductListingCommandHandler::class, $result);
    }

    public function testAddImageCommandHandlerIsReturned(): void
    {
        $result = $this->commonFactory->createAddImageCommandHandler();
        $this->assertInstanceOf(AddImageCommandHandler::class, $result);
    }

    public function testReturnsShutdownWorkerCommandHandler(): void
    {
        $result = $this->commonFactory->createShutdownWorkerCommandHandler();
        $this->assertInstanceOf(ShutdownWorkerDirectiveHandler::class, $result);
    }
    
    public function testReturnsAnImportCatalogCommandHandler(): void
    {
        $result = $this->commonFactory->createImportCatalogCommandHandler();
        $this->assertInstanceOf(ImportCatalogCommandHandler::class, $result);
    }
    
    public function testReturnsASetCurrentDataVersionCommandHandler(): void
    {
        $result = $this->commonFactory->createSetCurrentDataVersionCommandHandler();
        $this->assertInstanceOf(SetCurrentDataVersionCommandHandler::class, $result);
    }

    public function testContentBlockInProductListingSnippetKeyGeneratorIsReturned(): void
    {
        $result = $this->commonFactory->createContentBlockInProductListingSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testProductSearchResultMetaSnippetKeyGeneratorIsReturned(): void
    {
        $result = $this->commonFactory->createProductSearchResultMetaSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testImageProcessorCollectionIsReturned(): void
    {
        $result = $this->commonFactory->getImageProcessorCollection();
        $this->assertInstanceOf(ImageProcessorCollection::class, $result);
    }

    public function testSameInstanceOfImageProcessorCollectionIsReturnedOnConsecutiveCalls(): void
    {
        $resultA = $this->commonFactory->getImageProcessorCollection();
        $resultB = $this->commonFactory->getImageProcessorCollection();

        $this->assertSame($resultA, $resultB);
    }
    
    public function testCatalogImportIsReturned(): void
    {
        $result = $this->commonFactory->createCatalogImport();
        $this->assertInstanceOf(CatalogImport::class, $result);
    }

    public function testUrlKeyCollectorIsReturned(): void
    {
        $result = $this->commonFactory->createUrlKeyForContextCollector();
        $this->assertInstanceOf(UrlKeyForContextCollector::class, $result);
    }

    public function testItReturnsTheSameUrlKeyStoreInstance(): void
    {
        $result1 = $this->commonFactory->getUrlKeyStore();
        $result2 = $this->commonFactory->getUrlKeyStore();
        $this->assertSame($result1, $result2);
    }

    public function testProductDetailsViewTranslatorFactoryIsReturningATranslator(): void
    {
        $translatorFactory = $this->commonFactory->getProductDetailsViewTranslatorFactory();
        $this->assertInstanceOf(Translator::class, $translatorFactory('en_US'));
    }

    public function testProductListingTranslatorFactoryIsReturningATranslator(): void
    {
        $translatorFactory = $this->commonFactory->getProductListingTranslatorFactory();
        $this->assertInstanceOf(Translator::class, $translatorFactory('en_US'));
    }

    public function testItReturnsAConfigReader(): void
    {
        $result = $this->commonFactory->createConfigReader();
        $this->assertInstanceOf(ConfigReader::class, $result);
    }

    public function testItReturnsACatalogWasImportedDomainEventHandler(): void
    {
        $result = $this->commonFactory->createCatalogWasImportedDomainEventHandler();
        $this->assertInstanceOf(CatalogWasImportedDomainEventHandler::class, $result);
    }

    public function testReturnsAShutdownWorkerDomainEventHandler(): void
    {
        $result = $this->commonFactory->createShutdownWorkerDomainEventHandler();
        $this->assertInstanceOf(ShutdownWorkerDirectiveHandler::class, $result);
    }

    public function testReturnsACatalogImportWasTriggeredDomainEventHandler(): void
    {
        $result = $this->commonFactory->createCatalogImportWasTriggeredDomainEventHandler();
        $this->assertInstanceOf(CatalogImportWasTriggeredDomainEventHandler::class, $result);
    }

    public function testReturnsACurrentDataVersionWasSetDomainEventHandler(): void
    {
        $result = $this->commonFactory->createCurrentDataVersionWasSetDomainEventHandler();
        $this->assertInstanceOf(CurrentDataVersionWasSetDomainEventHandler::class, $result);
    }

    public function testItReturnsAProductJsonSnippetRenderer(): void
    {
        $result = $this->commonFactory->createProductJsonSnippetRenderer();
        $this->assertInstanceOf(ProductJsonSnippetRenderer::class, $result);
    }

    public function testItReturnsAProductJsonSnippetKeyGenerator(): void
    {
        $result = $this->commonFactory->createProductJsonSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItReturnsAConfigurableProductJsonSnippetRenderer(): void
    {
        $result = $this->commonFactory->createConfigurableProductJsonSnippetRenderer();
        $this->assertInstanceOf(ConfigurableProductJsonSnippetRenderer::class, $result);
    }

    public function testItReturnsAConfigurableProductVariationAttributesJsonSnippetKeyGenerator(): void
    {
        $result = $this->commonFactory->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItReturnsAConfigurableProductAssociatedProductsJsonSnippetKeyGenerator(): void
    {
        $result = $this->commonFactory->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testReturnsFacetFieldTransformationRegistry(): void
    {
        $result = $this->commonFactory->getFacetFieldTransformationRegistry();
        $this->assertInstanceOf(FacetFieldTransformationRegistry::class, $result);
    }

    public function testMemoizesFacetFieldTransformationRegistry(): void
    {
        $resultA = $this->commonFactory->getFacetFieldTransformationRegistry();
        $resultB = $this->commonFactory->getFacetFieldTransformationRegistry();

        $this->assertSame($resultA, $resultB);
    }

    public function testSnippetKeyGeneratorForContentBlockIsReturned(): void
    {
        $snippetCode = 'content_block_foo';
        $snippetKeyGeneratorLocator = $this->commonFactory->createContentBlockSnippetKeyGeneratorLocatorStrategy();
        $result = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testSnippetKeyGeneratorForProductListingContentBlockIsReturned(): void
    {
        $snippetCode = 'product_listing_content_block_foo';
        $snippetKeyGeneratorLocator = $this->commonFactory->createContentBlockSnippetKeyGeneratorLocatorStrategy();
        $result = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItReturnsABaseUrlBuilder(): void
    {
        $result = $this->commonFactory->createBaseUrlBuilder();
        $this->assertInstanceOf(BaseUrlBuilder::class, $result);
    }

    public function testItReturnsAVersionContextPartBuilder(): void
    {
        $result = $this->commonFactory->createVersionContextPartBuilder();
        $this->assertInstanceOf(ContextPartBuilder::class, $result);
        $this->assertInstanceOf(ContextVersion::class, $result);
    }

    public function testItReturnsSameInstanceOfWebsiteContextPartBuilder(): void
    {
        $builderA = $this->commonFactory->getWebsiteContextPartBuilder();
        $builderB = $this->commonFactory->getWebsiteContextPartBuilder();

        $this->assertSame($builderA, $builderB);
        $this->assertInstanceOf(ContextPartBuilder::class, $builderA);
    }

    public function testItReturnsSameInstanceOfLocaleContextPartBuilder(): void
    {
        $builderA = $this->commonFactory->getLocaleContextPartBuilder();
        $builderB = $this->commonFactory->getLocaleContextPartBuilder();

        $this->assertSame($builderA, $builderB);
        $this->assertInstanceOf(ContextPartBuilder::class, $builderA);
    }

    public function testItReturnsSameInstanceOfCountryContextPartBuilder(): void
    {
        $builderA = $this->commonFactory->getCountryContextPartBuilder();
        $builderB = $this->commonFactory->getCountryContextPartBuilder();

        $this->assertSame($builderA, $builderB);
        $this->assertInstanceOf(ContextPartBuilder::class, $builderA);
    }

    public function testItReturnsAFilesystemFileStorage(): void
    {
        $this->assertInstanceOf(FilesystemFileStorage::class, $this->commonFactory->createFilesystemFileStorage());
    }

    public function testItReturnsTheDefaultMediaBaseDirectoryConfiguration(): void
    {
        $path = preg_replace('#tests/Unit/Suites#', 'src', __DIR__);
        $baseDirectory = $this->commonFactory->getMediaBaseDirectoryConfig();

        $this->assertSame($path . '/../pub/media', $baseDirectory);
    }

    public function testItReturnsTheConfiguredMediaBaseDirectoryConfiguration(): void
    {
        $configuredBaseMediaPath = '/foo/bar';

        $originalState = $_SERVER;
        $_SERVER['LP_MEDIA_BASE_PATH'] = $configuredBaseMediaPath;

        $baseDirectory = $this->commonFactory->getMediaBaseDirectoryConfig();

        $_SERVER = $originalState;

        $this->assertSame($configuredBaseMediaPath, $baseDirectory);
    }

    public function testItReturnsAMediaDirectoryBaseUrlBuilderinstance(): void
    {
        $result = $this->commonFactory->createMediaBaseUrlBuilder();
        $this->assertInstanceOf(MediaBaseUrlBuilder::class, $result);
    }

    public function testItReturnsAnAttributeValueCollectorLocator(): void
    {
        $result = $this->commonFactory->createAttributeValueCollectorLocator();
        $this->assertInstanceOf(AttributeValueCollectorLocator::class, $result);
    }

    public function testItReturnsADefaultAttributeValueCollector(): void
    {
        $result = $this->commonFactory->createDefaultAttributeValueCollector();
        $this->assertInstanceOf(DefaultAttributeValueCollector::class, $result);
    }

    public function testItReturnsAConfigurableProductAttributeValueCollector(): void
    {
        $result = $this->commonFactory->createConfigurableProductAttributeValueCollector();
        $this->assertInstanceOf(ConfigurableProductAttributeValueCollector::class, $result);
    }

    public function testItReturnsAQueueImportCommandsInstance(): void
    {
        $result = $this->commonFactory->createQueueImportCommands();
        $this->assertInstanceOf(QueueImportCommands::class, $result);
    }

    public function testItReturnsAProductImportCommandLocator(): void
    {
        $result = $this->commonFactory->createProductImportCommandLocator();
        $this->assertInstanceOf(ProductImportCommandLocator::class, $result);
    }

    public function testItReturnsAProductImageImportCommandLocator(): void
    {
        $result = $this->commonFactory->createProductImageImportCommandLocator();
        $this->assertInstanceOf(ProductImageImportCommandLocator::class, $result);
    }

    public function testItReturnsAProductListingImportCommandLocator(): void
    {
        $result = $this->commonFactory->createProductListingImportCommandLocator();
        $this->assertInstanceOf(ProductListingImportCommandLocator::class, $result);
    }

    /**
     * @dataProvider productListSnippetRenderersProvider
     */
    public function testContainsProductListingPageSnippetRenderersInSnippetRendererList(string $expected): void
    {
        $found = array_reduce(
            $this->commonFactory->createProductListingSnippetRendererList(),
            function ($found, SnippetRenderer $snippetRenderer) use ($expected) {
                return $found || is_a($snippetRenderer, $expected);
            }
        );
        $message = sprintf('SnippetRenderer "%s" not found in product listing snippet renderer list', $expected);
        $this->assertTrue($found, $message);
    }

    /**
     * @return array[]
     */
    public function productListSnippetRenderersProvider() : array
    {
        return [
            [ProductListingMetaSnippetRenderer::class],
        ];
    }

    /**
     * @dataProvider productSnippetRenderersProvider
     */
    public function testContainsProductSnippetRenderersInSnippetRendererList(string $expected): void
    {
        $found = array_reduce(
            $this->commonFactory->createProductDetailPageSnippetRendererList(),
            function ($found, SnippetRenderer $snippetRenderer) use ($expected) {
                return $found || is_a($snippetRenderer, $expected);
            }
        );
        $message = sprintf('SnippetRenderer "%s" not found in product detail snippet renderer list', $expected);
        $this->assertTrue($found, $message);
    }

    /**
     * @return array[]
     */
    public function productSnippetRenderersProvider() : array
    {
        return [
            [ProductDetailMetaSnippetRenderer::class],
            [PriceSnippetRenderer::class],
            [ProductJsonSnippetRenderer::class],
            [ConfigurableProductJsonSnippetRenderer::class],
        ];
    }

    /**
     * @dataProvider productListingTemplateSnippetRenderersProvider
     */
    public function testContainsProductListingTemplateSnippetRenderersInSnippetRendererList(string $expected): void
    {
        $found = array_reduce(
            $this->commonFactory->createProductListingTemplateSnippetRendererList(),
            function ($found, SnippetRenderer $snippetRenderer) use ($expected) {
                return $found || is_a($snippetRenderer, $expected);
            }
        );
        $message = sprintf(
            'SnippetRenderer "%s" not found in product listing template snippet renderer list',
            $expected
        );
        $this->assertTrue($found, $message);
    }

    /**
     * @return array[]
     */
    public function productListingTemplateSnippetRenderersProvider() : array
    {
        return [
            [ProductListingTemplateSnippetRenderer::class],
            [ProductSearchResultMetaSnippetRenderer::class],
        ];
    }

    /**
     * @dataProvider productDetailTemplateSnippetRenderersProvider
     */
    public function testContainsProductDetailTemplateSnippetRenderersInSnippetRendererList(string $expected): void
    {
        $found = array_reduce(
            $this->commonFactory->createProductDetailTemplateSnippetRendererList(),
            function ($found, SnippetRenderer $snippetRenderer) use ($expected) {
                return $found || is_a($snippetRenderer, $expected);
            }
        );
        $message = sprintf(
            'SnippetRenderer "%s" not found in product detail template snippet renderer list',
            $expected
        );
        $this->assertTrue($found, $message);
    }

    /**
     * @return array[]
     */
    public function productDetailTemplateSnippetRenderersProvider() : array
    {
        return [
            [ProductDetailTemplateSnippetRenderer::class],
        ];
    }

    /**
     * @dataProvider contentBlockSnippetRenderersProvider
     */
    public function testContainsContentBlockSnippetRenderersInSnippetRendererList(string $expected): void
    {
        $found = array_reduce(
            $this->commonFactory->createContentBlockSnippetRendererList(),
            function ($found, SnippetRenderer $snippetRenderer) use ($expected) {
                return $found || is_a($snippetRenderer, $expected);
            }
        );
        $message = sprintf('SnippetRenderer "%s" not found in content block snippet renderer list', $expected);
        $this->assertTrue($found, $message);
    }

    /**
     * @return array[]
     */
    public function contentBlockSnippetRenderersProvider() : array
    {
        return [
            [ContentBlockSnippetRenderer::class],
        ];
    }

    public function testItReturnsAProductJsonService(): void
    {
        $result = $this->commonFactory->createProductJsonService();
        $this->assertInstanceOf(ProductJsonService::class, $result);
    }

    public function testItReturnsAnEnrichProductJsonWithPrices(): void
    {
        $result = $this->commonFactory->createEnrichProductJsonWithPrices();
        $this->assertInstanceOf(EnrichProductJsonWithPrices::class, $result);
    }

    public function testReturnsSnippetReader(): void
    {
        $this->assertInstanceOf(SnippetReader::class, $this->commonFactory->createSnippetReader());
    }
}
