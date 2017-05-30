<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\DataPool\DataVersion\CurrentDataVersionWasSetDomainEventHandler;
use LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEventHandler;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Logging\LoggingDomainEventHandlerFactory
 * @uses \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses \LizardsAndPumpkins\Import\CatalogImport
 * @uses \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEventHandler
 * @uses \LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector
 * @uses \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetRenderer
 * @uses \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler
 * @uses \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler
 * @uses \LizardsAndPumpkins\Import\Price\PriceSnippetRenderer
 * @uses \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @uses \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 * @uses \LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator
 * @uses \LizardsAndPumpkins\Import\Product\ProductImportCommandLocator
 * @uses \LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer
 * @uses \LizardsAndPumpkins\Import\Product\ProductProjector
 * @uses \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler
 * @uses \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses \LizardsAndPumpkins\Import\Product\QueueImportCommands
 * @uses \LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer
 * @uses \LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector
 * @uses \LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator
 * @uses \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler
 * @uses \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses \LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer
 * @uses \LizardsAndPumpkins\Logging\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler
 * @uses \LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope
 * @uses \LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer
 * @uses \LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer
 * @uses \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses \LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer
 * @uses \LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer
 * @uses \LizardsAndPumpkins\ProductListing\Import\ProductListingProjector
 * @uses \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer
 * @uses \LizardsAndPumpkins\Import\GenericSnippetProjector
 * @uses \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer
 * @uses \LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer
 * @uses \LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer
 * @uses \LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer
 * @uses \LizardsAndPumpkins\ProductListing\ProductListingCanonicalTagSnippetRenderer
 * @uses \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler
 * @uses \LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator
 * @uses \LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder
 * @uses \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class LoggingDomainEventHandlerFactoryTest extends TestCase
{
    /**
     * @var LoggingDomainEventHandlerFactory
     */
    private $factory;

    private function assertDecoratedDomainEventHandlerInstanceOf(string $expectedClassName, DomainEventHandler $actual)
    {
        $this->assertInstanceOf(ProcessTimeLoggingDomainEventHandlerDecorator::class, $actual);
        $this->assertAttributeInstanceOf($expectedClassName, 'component', $actual);
    }

    protected function setUp()
    {
        $masterFactory = new CatalogMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);
        $masterFactory->register(new UnitTestFactory($this));
        $this->factory = new LoggingDomainEventHandlerFactory($masterFactory);
        $masterFactory->register($this->factory);
    }

    public function testItReturnsADecoratedProductWasUpdatedDomainEventHandler()
    {
        $result = $this->factory->createProductWasUpdatedDomainEventHandler();
        $this->assertDecoratedDomainEventHandlerInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedTemplateWasUpdatedDomainEventHandler()
    {
        $result = $this->factory->createTemplateWasUpdatedDomainEventHandler();
        $this->assertDecoratedDomainEventHandlerInstanceOf(TemplateWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedImageWasAddedDomainEventHandler()
    {
        $result = $this->factory->createImageWasAddedDomainEventHandler();
        $this->assertDecoratedDomainEventHandlerInstanceOf(ImageWasAddedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedProductListingWasAddedDomainEventHandler()
    {
        $result = $this->factory->createProductListingWasAddedDomainEventHandler();
        $this->assertDecoratedDomainEventHandlerInstanceOf(ProductListingWasAddedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedContentBlockWasUpdatedDomainEventHandler()
    {
        $result = $this->factory->createContentBlockWasUpdatedDomainEventHandler();
        $this->assertDecoratedDomainEventHandlerInstanceOf(ContentBlockWasUpdatedDomainEventHandler::class, $result);
    }

    public function testItReturnsADecoratedCatalogWasImportedDomainEventHandler()
    {
        $result = $this->factory->createCatalogWasImportedDomainEventHandler();
        $this->assertDecoratedDomainEventHandlerInstanceOf(CatalogWasImportedDomainEventHandler::class, $result);
    }

    public function testReturnsADecoratedShutdownWorkerDirectiveHandler()
    {
        $result = $this->factory->createShutdownWorkerDomainEventHandler();
        $this->assertDecoratedDomainEventHandlerInstanceOf(ShutdownWorkerDirectiveHandler::class, $result);
    }

    public function testReturnsADecoratedCatalogImportWasTriggeredDomainEventHandler()
    {
        $result = $this->factory->createCatalogImportWasTriggeredDomainEventHandler();
        $this->assertDecoratedDomainEventHandlerInstanceOf(CatalogImportWasTriggeredDomainEventHandler::class, $result);
    }

    public function testReturnsADecoratedCurrentDataVersionWasSetDomainEventHandler()
    {
        $result = $this->factory->createCurrentDataVersionWasSetDomainEventHandler();
        $this->assertDecoratedDomainEventHandlerInstanceOf(CurrentDataVersionWasSetDomainEventHandler::class, $result);
    }
}
