<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\DataVersion\ContextVersion;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContext;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Http\Routing\ResourceNotFoundRouter;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockId;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler;
use LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage;
use LizardsAndPumpkins\Import\Image\AddImageCommand;
use LizardsAndPumpkins\Import\Image\AddImageCommandHandler;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Import\ImageStorage\MediaBaseUrlBuilder;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\ProductImportCommandLocator;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductProjector;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\Product\QueueImportCommands;
use LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\Product\UpdateProductCommand;
use LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator;
use LizardsAndPumpkins\Logging\ProcessTimeLoggingDomainEventHandlerDecorator;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerLocator;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Command\ShutdownWorkerCommand;
use LizardsAndPumpkins\Messaging\Command\ShutdownWorkerCommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandlerLocator;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductCanonicalTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\ProductListing\AddProductListingCommand;
use LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder;
use LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingDescriptionBlockRenderer;
use LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator;
use LizardsAndPumpkins\ProductSearch\Import\ConfigurableProductAttributeValueCollector;
use LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Factory\Exception\NoMasterFactorySetException;
use LizardsAndPumpkins\Util\Factory\Exception\UndefinedFactoryMethodException;

/**
 * @covers \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @covers \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommandHandler
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
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
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateProjector
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetProjector
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingBuilder
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer
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
 * @uses   \LizardsAndPumpkins\Import\SnippetRendererCollection
 * @uses   \LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessor
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategySequence
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageReader
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\Logging\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator
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
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator
 * @uses   \LizardsAndPumpkins\Translation\CsvTranslator
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\MediaDirectoryBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductCanonicalTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingCanonicalTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer
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
 * @uses   \LizardsAndPumpkins\Messaging\Command\ShutdownWorkerCommand
 * @uses   \LizardsAndPumpkins\Messaging\Command\ShutdownWorkerCommandHandler
 */
class CommonFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommonFactory
     */
    private $commonFactory;

    protected function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new UnitTestFactory($this));
        $this->commonFactory = new CommonFactory();
        $masterFactory->register($this->commonFactory);
    }

    public function testExceptionIsThrownIfNoMasterFactoryIsSet()
    {
        $this->expectException(NoMasterFactorySetException::class);
        (new CommonFactory())->createDomainEventConsumer();
    }

    public function testProductWasUpdatedDomainEventHandlerIsReturned()
    {
        $testProduct = new SimpleProduct(
            new ProductId('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            new SelfContainedContext([DataVersion::CONTEXT_CODE => 'buz'])
        );
        $testEvent = new ProductWasUpdatedDomainEvent($testProduct);
        $result = $this->commonFactory->createProductWasUpdatedDomainEventHandler($testEvent->toMessage());

        $this->assertInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testTemplateWasUpdatedDomainEventHandlerIsReturned()
    {
        $testEvent = new TemplateWasUpdatedDomainEvent('foo', 'bar');
        $result = $this->commonFactory->createTemplateWasUpdatedDomainEventHandler($testEvent->toMessage());

        $this->assertInstanceOf(TemplateWasUpdatedDomainEventHandler::class, $result);
    }

    public function testProductListingWasAddedDomainEventHandlerIsReturned()
    {
        /** @var ProductListing|\PHPUnit_Framework_MockObject_MockObject $stubProductListing */
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubProductListing->method('getContextData')->willReturn([DataVersion::CONTEXT_CODE => 'foo']);
        $stubProductListing->method('serialize')->willReturn(serialize($stubProductListing));
        $testEvent = new ProductListingWasAddedDomainEvent($stubProductListing);
        $result = $this->commonFactory->createProductListingWasAddedDomainEventHandler($testEvent->toMessage());

        $this->assertInstanceOf(ProductListingWasAddedDomainEventHandler::class, $result);
    }

    public function testProductProjectorIsReturned()
    {
        $result = $this->commonFactory->createProductProjector();
        $this->assertInstanceOf(ProductProjector::class, $result);
    }

    public function testProductDetailViewSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createProductDetailViewSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testProductTitleSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createProductTitleSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testProductXmlToProductBuilderLocatorIsReturned()
    {
        $result = $this->commonFactory->createProductXmlToProductBuilderLocator();
        $this->assertInstanceOf(ProductXmlToProductBuilderLocator::class, $result);
    }

    public function testProductXmlToProductBuilderLocatorProxyFactoryIsReturned()
    {
        $proxy = $this->commonFactory->createProductXmlToProductBuilderLocatorProxyFactoryMethod();
        $this->assertInstanceOf(ProductXmlToProductBuilderLocator::class, $proxy());
    }

    public function testProductListingBuilderIsReturned()
    {
        $result = $this->commonFactory->createProductListingBuilder();
        $this->assertInstanceOf(ProductListingBuilder::class, $result);
    }

    public function testContextBuilderIsReturned()
    {
        $result = $this->commonFactory->createContextBuilder();
        $this->assertInstanceOf(ContextBuilder::class, $result);
    }

    public function testDomainEventHandlerLocatorIsReturned()
    {
        $result = $this->commonFactory->createDomainEventHandlerLocator();
        $this->assertInstanceOf(DomainEventHandlerLocator::class, $result);
    }

    public function testDataPoolWriterIsReturned()
    {
        $result = $this->commonFactory->createDomainEventHandlerLocator();
        $this->assertInstanceOf(DomainEventHandlerLocator::class, $result);
    }

    public function testDomainEventConsumerIsReturned()
    {
        $result = $this->commonFactory->createDomainEventConsumer();
        $this->assertInstanceOf(DomainEventConsumer::class, $result);
    }

    public function testDomainEventQueueIsReturned()
    {
        $result = $this->commonFactory->getEventQueue();
        $this->assertInstanceOf(DomainEventQueue::class, $result);
    }

    public function testSameDomainEventQueueInstanceIsReturned()
    {
        $result1 = $this->commonFactory->getEventQueue();
        $result2 = $this->commonFactory->getEventQueue();
        $this->assertSame($result1, $result2);
    }

    public function testDomainEventMessageQueueIsReturned()
    {
        $result = $this->commonFactory->getEventMessageQueue();
        $this->assertInstanceOf(Queue::class, $result);
    }

    public function testSameDomainEventMessageQueueInstanceIsReturned()
    {
        $result1 = $this->commonFactory->getEventMessageQueue();
        $result2 = $this->commonFactory->getEventMessageQueue();
        $this->assertSame($result1, $result2);
    }

    public function testDataPoolReaderIsReturned()
    {
        $result = $this->commonFactory->createDataPoolReader();
        $this->assertInstanceOf(DataPoolReader::class, $result);
    }

    public function testExceptionWithHelpfulMessageIsThrownIfNoKeyValueStoreFactoryIsRegistered()
    {
        $masterFactory = new SampleMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $this->expectException(UndefinedFactoryMethodException::class);
        $this->expectExceptionMessage('Unable to create KeyValueStore. Is the factory registered?');

        $commonFactory->createDataPoolReader();
    }

    public function testExceptionWithHelpfulMessageIsThrownIfNoEventQueueFactoryIsRegistered()
    {
        $masterFactory = new SampleMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $this->expectException(UndefinedFactoryMethodException::class);
        $this->expectExceptionMessage('Unable to create EventQueue. Is the factory registered?');

        $commonFactory->getEventQueue();
    }

    public function testExceptionWithHelpfulMessageIsThrownIfNoLoggerFactoryIsRegistered()
    {
        $masterFactory = new SampleMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);

        $this->expectException(UndefinedFactoryMethodException::class);
        $this->expectExceptionMessage('Unable to create Logger. Is the factory registered?');

        $commonFactory->getLogger();
    }

    public function testLoggerInstanceIsReturned()
    {
        $resultA = $this->commonFactory->getLogger();
        $resultB = $this->commonFactory->getLogger();
        $this->assertInstanceOf(Logger::class, $resultA);
        $this->assertSame($resultA, $resultB);
    }

    public function testResourceNotFoundRouterIsReturned()
    {
        $result = $this->commonFactory->createResourceNotFoundRouter();
        $this->assertInstanceOf(ResourceNotFoundRouter::class, $result);
    }

    public function testHttpRouterChainIsReturned()
    {
        $result = $this->commonFactory->createHttpRouterChain();
        $this->assertInstanceOf(HttpRouterChain::class, $result);
    }

    public function testImageImportEventDomainHandlerIsReturned()
    {
        $testEvent = new ImageWasAddedDomainEvent('foo', DataVersion::fromVersionString('foo'));
        $result = $this->commonFactory->createImageWasAddedDomainEventHandler($testEvent->toMessage());

        $this->assertInstanceOf(ImageWasAddedDomainEventHandler::class, $result);
    }

    public function testSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createProductInListingSnippetKeyGenerator();
        $this->assertInstanceOf(GenericSnippetKeyGenerator::class, $result);
    }

    public function testCommandConsumerIsReturned()
    {
        $result = $this->commonFactory->createCommandConsumer();
        $this->assertInstanceOf(CommandConsumer::class, $result);
    }

    public function testCommandQueueIsReturned()
    {
        $result = $this->commonFactory->getCommandQueue();
        $this->assertInstanceOf(CommandQueue::class, $result);
    }

    public function testSameCommandQueueInstanceIsReturned()
    {
        $result1 = $this->commonFactory->getCommandQueue();
        $result2 = $this->commonFactory->getCommandQueue();

        $this->assertSame($result1, $result2);
    }

    public function testReturnsCommandMessageQueue()
    {
        $result = $this->commonFactory->getCommandMessageQueue();
        $this->assertInstanceOf(Queue::class, $result);
    }

    public function testReturnsSameCommandMessageQueueInstance()
    {
        $result1 = $this->commonFactory->getCommandMessageQueue();
        $result2 = $this->commonFactory->getCommandMessageQueue();

        $this->assertSame($result1, $result2);
    }

    public function testCommandHandlerLocatorIsReturned()
    {
        $result = $this->commonFactory->createCommandHandlerLocator();
        $this->assertInstanceOf(CommandHandlerLocator::class, $result);
    }

    public function testUpdateContentBlockCommandHandlerIsReturned()
    {
        $contentBlockSource = new ContentBlockSource(ContentBlockId::fromString('foo'), '', [], []);
        $sourceCommand = new UpdateContentBlockCommand($contentBlockSource);
        $message = $sourceCommand->toMessage();
        $result = $this->commonFactory->createUpdateContentBlockCommandHandler($message);

        $this->assertInstanceOf(UpdateContentBlockCommandHandler::class, $result);
    }

    public function testContentBlockWasUpdatedDomainEventHandlerIsReturned()
    {
        $testContentBlockSource = new ContentBlockSource(
            ContentBlockId::fromString('foo'),
            '',
            [],
            []
        );
        $testEvent = new ContentBlockWasUpdatedDomainEvent($testContentBlockSource);
        $result = $this->commonFactory->createContentBlockWasUpdatedDomainEventHandler($testEvent->toMessage());

        $this->assertInstanceOf(ContentBlockWasUpdatedDomainEventHandler::class, $result);
    }

    public function testUpdateProductCommandHandlerIsReturned()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('jsonSerialize')->willReturn([DataVersion::CONTEXT_CODE => '123']);
        $stubContext->method('getValue')->willReturn('123');
        $product = new SimpleProduct(
            new ProductId('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            $stubContext
        );
        $sourceCommand = new UpdateProductCommand($product);
        $result = $this->commonFactory->createUpdateProductCommandHandler($sourceCommand->toMessage());

        $this->assertInstanceOf(UpdateProductCommandHandler::class, $result);
    }

    public function testAddProductListingCommandHandlerIsReturned()
    {
        /** @var ProductListing|\PHPUnit_Framework_MockObject_MockObject $stubProductListing */
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubProductListing->method('serialize')->willReturn(serialize($stubProductListing));
        $sourceCommand = new AddProductListingCommand($stubProductListing);
        $result = $this->commonFactory->createAddProductListingCommandHandler($sourceCommand->toMessage());

        $this->assertInstanceOf(AddProductListingCommandHandler::class, $result);
    }

    public function testAddImageCommandHandlerIsReturned()
    {
        $sourceCommand = new AddImageCommand(__FILE__, DataVersion::fromVersionString('foo bar'));
        $result = $this->commonFactory->createAddImageCommandHandler($sourceCommand->toMessage());

        $this->assertInstanceOf(AddImageCommandHandler::class, $result);
    }

    public function testReturnsShutdownWorkerCommandHandler()
    {
        $sourceCommand = new ShutdownWorkerCommand('*');
        $result = $this->commonFactory->createShutdownWorkerCommandHandler($sourceCommand->toMessage());

        $this->assertInstanceOf(ShutdownWorkerCommandHandler::class, $result);
    }

    public function testContentBlockInProductListingSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createContentBlockInProductListingSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testProductSearchResultMetaSnippetKeyGeneratorIsReturned()
    {
        $result = $this->commonFactory->createProductSearchResultMetaSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testImageProcessorCollectionIsReturned()
    {
        $result = $this->commonFactory->getImageProcessorCollection();
        $this->assertInstanceOf(ImageProcessorCollection::class, $result);
    }

    public function testSameInstanceOfImageProcessorCollectionIsReturnedOnConsecutiveCalls()
    {
        $resultA = $this->commonFactory->getImageProcessorCollection();
        $resultB = $this->commonFactory->getImageProcessorCollection();

        $this->assertSame($resultA, $resultB);
    }

    public function testItReturnsAProcessTimeLoggingDomainEventHandlerDecorator()
    {
        $testEvent = new ImageWasAddedDomainEvent('foo', DataVersion::fromVersionString('foo'));
        $eventHandlerToDecorate = $this->commonFactory->createImageWasAddedDomainEventHandler($testEvent->toMessage());
        $result = $this->commonFactory->createProcessTimeLoggingDomainEventHandlerDecorator($eventHandlerToDecorate);
        $this->assertInstanceOf(ProcessTimeLoggingDomainEventHandlerDecorator::class, $result);
    }

    public function testItReturnsAProcessTimeLoggingCommandHandlerDecorator()
    {
        $sourceCommand = new AddImageCommand(__FILE__, DataVersion::fromVersionString('123'));
        
        $commandHandlerToDecorate = $this->commonFactory->createAddImageCommandHandler($sourceCommand->toMessage());
        $result = $this->commonFactory->createProcessTimeLoggingCommandHandlerDecorator($commandHandlerToDecorate);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $result);
    }

    public function testCatalogImportIsReturned()
    {
        $result = $this->commonFactory->createCatalogImport();
        $this->assertInstanceOf(CatalogImport::class, $result);
    }

    public function testUrlKeyCollectorIsReturned()
    {
        $result = $this->commonFactory->createUrlKeyForContextCollector();
        $this->assertInstanceOf(UrlKeyForContextCollector::class, $result);
    }

    public function testItReturnsTheSameUrlKeyStoreInstance()
    {
        $result1 = $this->commonFactory->getUrlKeyStore();
        $result2 = $this->commonFactory->getUrlKeyStore();
        $this->assertSame($result1, $result2);
    }

    public function testProductDetailsViewTranslatorFactoryIsReturningATranslator()
    {
        $translatorFactory = $this->commonFactory->getProductDetailsViewTranslatorFactory();
        $this->assertInstanceOf(Translator::class, $translatorFactory('en_US'));
    }

    public function testProductListingTranslatorFactoryIsReturningATranslator()
    {
        $translatorFactory = $this->commonFactory->getProductListingTranslatorFactory();
        $this->assertInstanceOf(Translator::class, $translatorFactory('en_US'));
    }

    public function testItReturnsAConfigReader()
    {
        $result = $this->commonFactory->createConfigReader();
        $this->assertInstanceOf(ConfigReader::class, $result);
    }

    public function testItReturnsACatalogWasImportedDomainEventHandler()
    {
        $testEvent = new CatalogWasImportedDomainEvent(DataVersion::fromVersionString('foo'));
        $result = $this->commonFactory->createCatalogWasImportedDomainEventHandler($testEvent->toMessage());
        $this->assertInstanceOf(CatalogWasImportedDomainEventHandler::class, $result);
    }

    public function testItReturnsAProductJsonSnippetRenderer()
    {
        $result = $this->commonFactory->createProductJsonSnippetRenderer();
        $this->assertInstanceOf(ProductJsonSnippetRenderer::class, $result);
    }

    public function testItReturnsAProductJsonSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductJsonSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItReturnsAConfigurableProductJsonSnippetRenderer()
    {
        $result = $this->commonFactory->createConfigurableProductJsonSnippetRenderer();
        $this->assertInstanceOf(ConfigurableProductJsonSnippetRenderer::class, $result);
    }

    public function testItReturnsAConfigurableProductVariationAttributesJsonSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItReturnsAConfigurableProductAssociatedProductsJsonSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testSearchCriteriaBuilderIsReturned()
    {
        $result = $this->commonFactory->createSearchCriteriaBuilder();
        $this->assertInstanceOf(SearchCriteriaBuilder::class, $result);
    }

    public function testSnippetKeyGeneratorForContentBlockIsReturned()
    {
        $snippetCode = 'content_block_foo';
        $snippetKeyGeneratorLocator = $this->commonFactory->createContentBlockSnippetKeyGeneratorLocatorStrategy();
        $result = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testSnippetKeyGeneratorForProductListingContentBlockIsReturned()
    {
        $snippetCode = 'product_listing_content_block_foo';
        $snippetKeyGeneratorLocator = $this->commonFactory->createContentBlockSnippetKeyGeneratorLocatorStrategy();
        $result = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItReturnsABaseUrlBuilder()
    {
        $result = $this->commonFactory->createBaseUrlBuilder();
        $this->assertInstanceOf(BaseUrlBuilder::class, $result);
    }

    public function testItReturnsAVersionContextPartBuilder()
    {
        $result = $this->commonFactory->createVersionContextPartBuilder();
        $this->assertInstanceOf(ContextPartBuilder::class, $result);
        $this->assertInstanceOf(ContextVersion::class, $result);
    }

    public function testItReturnsSameInstanceOfWebsiteContextPartBuilder()
    {
        $builderA = $this->commonFactory->getWebsiteContextPartBuilder();
        $builderB = $this->commonFactory->getWebsiteContextPartBuilder();

        $this->assertSame($builderA, $builderB);
        $this->assertInstanceOf(ContextPartBuilder::class, $builderA);
    }

    public function testItReturnsSameInstanceOfLocaleContextPartBuilder()
    {
        $builderA = $this->commonFactory->getLocaleContextPartBuilder();
        $builderB = $this->commonFactory->getLocaleContextPartBuilder();

        $this->assertSame($builderA, $builderB);
        $this->assertInstanceOf(ContextPartBuilder::class, $builderA);
    }

    public function testItReturnsSameInstanceOfCountryContextPartBuilder()
    {
        $builderA = $this->commonFactory->getCountryContextPartBuilder();
        $builderB = $this->commonFactory->getCountryContextPartBuilder();

        $this->assertSame($builderA, $builderB);
        $this->assertInstanceOf(ContextPartBuilder::class, $builderA);
    }

    public function testItReturnsAFilesystemFileStorage()
    {
        $this->assertInstanceOf(FilesystemFileStorage::class, $this->commonFactory->createFilesystemFileStorage());
    }

    public function testItReturnsTheMediaBaseDirectoryConfiguration()
    {
        $baseDirectory = $this->commonFactory->getMediaBaseDirectoryConfig();
        $this->assertInternalType('string', $baseDirectory);
    }

    public function testItReturnsAMediaDirectoryBaseUrlBuilderinstance()
    {
        $result = $this->commonFactory->createMediaBaseUrlBuilder();
        $this->assertInstanceOf(MediaBaseUrlBuilder::class, $result);
    }

    public function testItReturnsAnAttributeValueCollectorLocator()
    {
        $result = $this->commonFactory->createAttributeValueCollectorLocator();
        $this->assertInstanceOf(AttributeValueCollectorLocator::class, $result);
    }

    public function testItReturnsADefaultAttributeValueCollector()
    {
        $result = $this->commonFactory->createDefaultAttributeValueCollector();
        $this->assertInstanceOf(DefaultAttributeValueCollector::class, $result);
    }

    public function testItReturnsAConfigurableProductAttributeValueCollector()
    {
        $result = $this->commonFactory->createConfigurableProductAttributeValueCollector();
        $this->assertInstanceOf(ConfigurableProductAttributeValueCollector::class, $result);
    }

    public function testItReturnsAQueueImportCommandsInstance()
    {
        $result = $this->commonFactory->createQueueImportCommands();
        $this->assertInstanceOf(QueueImportCommands::class, $result);
    }

    public function testItReturnsAProductImportCommandLocator()
    {
        $result = $this->commonFactory->createProductImportCommandLocator();
        $this->assertInstanceOf(ProductImportCommandLocator::class, $result);
    }

    public function testItReturnsAProductImageImportCommandLocator()
    {
        $result = $this->commonFactory->createProductImageImportCommandLocator();
        $this->assertInstanceOf(ProductImageImportCommandLocator::class, $result);
    }

    public function testItReturnsAProductListingImportCommandLocator()
    {
        $result = $this->commonFactory->createProductListingImportCommandLocator();
        $this->assertInstanceOf(ProductListingImportCommandLocator::class, $result);
    }

    public function testItCreatesAcreateProductListingTitleSnippetRenderer()
    {
        $result = $this->commonFactory->createProductListingTitleSnippetRenderer();
        $this->assertInstanceOf(ProductListingTitleSnippetRenderer::class, $result);
    }

    public function testItCreatesAProductListingTitleSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductListingTitleSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItCreatesAProductListingDescriptionSnippetRenderer()
    {
        $result = $this->commonFactory->createProductListingDescriptionSnippetRenderer();
        $this->assertInstanceOf(ProductListingDescriptionSnippetRenderer::class, $result);
    }

    public function testItCreatesAProductListingDescriptionSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductListingDescriptionSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testItCreatesAProductListingCanonicalTagSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductListingCanonicalTagSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    /**
     * @dataProvider productListSnippetRenderersProvider
     */
    public function testContainsProductListingPageSnippetRenderersInSnippetRendererList(string $expected)
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
            [ProductListingDescriptionSnippetRenderer::class],
            [ProductListingTitleSnippetRenderer::class],
            [ProductListingSnippetRenderer::class],
            [ProductListingRobotsMetaTagSnippetRenderer::class],
        ];
    }

    /**
     * @dataProvider productSnippetRenderersProvider
     */
    public function testContainsProductSnippetRenderersInSnippetRendererList(string $expected)
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
            [ProductDetailViewSnippetRenderer::class],
            [ProductInListingSnippetRenderer::class],
            [PriceSnippetRenderer::class],
            [ProductJsonSnippetRenderer::class],
            [ConfigurableProductJsonSnippetRenderer::class],
            [ProductCanonicalTagSnippetRenderer::class],
            [ProductDetailPageRobotsMetaTagSnippetRenderer::class],
        ];
    }

    public function testItReturnsAProductListingDescriptionBlockRenderer()
    {
        $result = $this->commonFactory->createProductListingDescriptionBlockRenderer();
        $this->assertInstanceOf(ProductListingDescriptionBlockRenderer::class, $result);
    }

    public function testItReturnsAProductDetailPageMetaDescriptionSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductDetailPageMetaDescriptionSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testReturnsProductCanonicalSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductCanonicalTagSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testReturnsProductCanonicalTagSnippetRenderer()
    {
        $result = $this->commonFactory->createProductCanonicalTagSnippetRenderer();
        $this->assertInstanceOf(ProductCanonicalTagSnippetRenderer::class, $result);
    }

    public function testReturnsProductDetailPageRobotsMetaTagAllSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductDetailPageRobotsMetaTagSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testReturnsProductListingPageRobotsMetaTagAllSnippetKeyGenerator()
    {
        $result = $this->commonFactory->createProductListingPageRobotsMetaTagSnippetKeyGenerator();
        $this->assertInstanceOf(SnippetKeyGenerator::class, $result);
    }

    public function testReturnsRobotsMetaTagSnippetRenderer()
    {
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $snippetKeyGenerator */
        $snippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $result = $this->commonFactory->createRobotsMetaTagSnippetRenderer($snippetKeyGenerator);
        $this->assertInstanceOf(RobotsMetaTagSnippetRenderer::class, $result);
    }

    public function testReturnsProductListingPageRobotsMetaTagSnippetRenderer()
    {
        $result = $this->commonFactory->createProductListingPageRobotsMetaTagSnippetRenderer();
        $this->assertInstanceOf(ProductListingRobotsMetaTagSnippetRenderer::class, $result);
    }

    public function testReturnsProductDetailPageRobotsMetaTagSnippetRenderer()
    {
        $result = $this->commonFactory->createProductDetailPageRobotsMetaTagSnippetRenderer();
        $this->assertInstanceOf(ProductDetailPageRobotsMetaTagSnippetRenderer::class, $result);
    }

    public function testItReturnsAProductJsonService()
    {
        $result = $this->commonFactory->createProductJsonService();
        $this->assertInstanceOf(ProductJsonService::class, $result);
    }

    public function testItReturnsAnEnrichProductJsonWithPrices()
    {
        $result = $this->commonFactory->createEnrichProductJsonWithPrices();
        $this->assertInstanceOf(EnrichProductJsonWithPrices::class, $result);
    }
}
