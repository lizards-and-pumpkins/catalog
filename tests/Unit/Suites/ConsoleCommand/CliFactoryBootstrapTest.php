<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use LizardsAndPumpkins\Logging\LoggingQueueDecorator;
use LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator;
use LizardsAndPumpkins\Logging\ProcessTimeLoggingDomainEventHandlerDecorator;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallbackTrait;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\CliFactoryBootstrap
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommandHandler
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommandHandler
 * @uses   \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ImportCatalogCommandHandler
 * @uses   \LizardsAndPumpkins\Import\Price\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\ProductProjector
 * @uses   \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Import\Product\QueueImportCommands
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommandHandler
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Logging\LoggingCommandHandlerFactory
 * @uses   \LizardsAndPumpkins\Logging\LoggingDomainEventHandlerFactory
 * @uses   \LizardsAndPumpkins\Logging\LoggingQueueDecorator
 * @uses   \LizardsAndPumpkins\Logging\LoggingQueueFactory
 * @uses   \LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator
 * @uses   \LizardsAndPumpkins\Logging\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler
 * @uses   \LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope
 * @uses   \LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\Import\ProductDetailTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingProjector
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\GenericSnippetProjector
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryWithCallbackTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class CliFactoryBootstrapTest extends TestCase
{
    private function createSpyFactory(): Factory
    {
        return new class implements FactoryWithCallback
        {
            use FactoryWithCallbackTrait;

            public $wasRegistered = false;

            public function factoryRegistrationCallback(MasterFactory $masterFactory)
            {
                $this->wasRegistered = true;
            }
        };
    }

    private function createSpyCommonFactory()
    {
        $spyCommonFactory = new class implements FactoryWithCallback
        {
            use FactoryWithCallbackTrait;

            private static $registrationCount = 0;

            public function factoryRegistrationCallback(MasterFactory $masterFactory)
            {
                static::$registrationCount++;
            }

            public function getRegistrationCount(): int
            {
                return static::$registrationCount;
            }

            public function resetRegistrationCount()
            {
                static::$registrationCount = 0;
            }
        };
        $spyCommonFactory->resetRegistrationCount();

        return $spyCommonFactory;
    }

    private function createTestCliFactoryBootstrap(): CliFactoryBootstrap
    {
        return new class extends CliFactoryBootstrap
        {
            private static $originalCommonFactory;

            public function setCommonFactoryClass(string $commonFactoryClass)
            {
                if (is_null(static::$originalCommonFactory)) {
                    static::$originalCommonFactory = static::$commonFactoryClass;
                }
                static::$commonFactoryClass = $commonFactoryClass;
            }

            public function __destruct()
            {
                if (!is_null(static::$originalCommonFactory)) {
                    static::$commonFactoryClass = static::$originalCommonFactory;
                }
            }
        };
    }

    public function testReturnsMasterFactoryInstance()
    {
        $this->assertInstanceOf(MasterFactory::class, (CliFactoryBootstrap::createMasterFactory()));
    }

    public function testRegistersAnySpecifiedFactories()
    {
        $spyFactoryA = $this->createSpyFactory();
        $spyFactoryB = $this->createSpyFactory();
        CliFactoryBootstrap::createMasterFactory($spyFactoryA, $spyFactoryB);
        $this->assertTrue($spyFactoryA->wasRegistered);
        $this->assertTrue($spyFactoryB->wasRegistered);
    }

    public function testRegistersCommonFactoryWithoutItBeingSpecified()
    {
        $testCliBootstrap = $this->createTestCliFactoryBootstrap();

        $spyCommonFactory = $this->createSpyCommonFactory();
        $testCliBootstrap->setCommonFactoryClass(get_class($spyCommonFactory));

        $testCliBootstrap->createMasterFactory();

        $this->assertSame(1, $spyCommonFactory->getRegistrationCount());
    }

    public function testDoesNotRegisterDefaultFactoryIfAlsoSpecifiedAsArgument()
    {
        $testCliBootstrap = $this->createTestCliFactoryBootstrap();

        $spyCommonFactory = $this->createSpyCommonFactory();
        $testCliBootstrap->setCommonFactoryClass(get_class($spyCommonFactory));

        $testCliBootstrap->createMasterFactory($spyCommonFactory);

        $this->assertSame(1, $spyCommonFactory->getRegistrationCount());
    }

    public function testReturnsAMasterFactoryWhenALoggingFactoryIsRequested()
    {
        $factory = CliFactoryBootstrap::createLoggingMasterFactory(new UnitTestFactory($this));
        $this->assertInstanceOf(MasterFactory::class, $factory);
    }

    public function testLoggingFactoriesAreRegistered()
    {
        $factory = CliFactoryBootstrap::createLoggingMasterFactory(new UnitTestFactory($this));

        $queue = $factory->createEventMessageQueue();
        $commandHandler = $factory->createUpdateContentBlockCommandHandler();
        $eventHandler = $factory->createTemplateWasUpdatedDomainEventHandler();

        $this->assertInstanceOf(LoggingQueueDecorator::class, $queue);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
        $this->assertInstanceOf(ProcessTimeLoggingDomainEventHandlerDecorator::class, $eventHandler);
    }
}

