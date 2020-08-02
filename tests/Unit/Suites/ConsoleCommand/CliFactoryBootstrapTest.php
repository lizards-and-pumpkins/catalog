<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use LizardsAndPumpkins\Core\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Core\Factory\Factory;
use LizardsAndPumpkins\Core\Factory\FactoryWithCallbackTrait;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
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
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Logging\LoggingQueueDecorator
 * @uses   \LizardsAndPumpkins\Messaging\Command\Logging\ProcessTimeLoggingCommandHandlerDecorator
 * @uses   \LizardsAndPumpkins\Messaging\Event\Logging\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler
 * @uses   \LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope
 * @uses   \LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\Import\ProductDetailTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingProjector
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\GenericSnippetProjector
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Core\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Core\Factory\FactoryWithCallbackTrait
 * @uses   \LizardsAndPumpkins\Core\Factory\MasterFactoryTrait
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

            public function factoryRegistrationCallback(MasterFactory $masterFactory): void
            {
                $this->wasRegistered = true;
            }
        };
    }

    private function createSpyCommonFactory(): FactoryWithCallback
    {
        $spyCommonFactory = new class implements FactoryWithCallback
        {
            use FactoryWithCallbackTrait;

            private static $registrationCount = 0;

            public function factoryRegistrationCallback(MasterFactory $masterFactory): void
            {
                static::$registrationCount++;
            }

            public function getRegistrationCount(): int
            {
                return static::$registrationCount;
            }

            public function resetRegistrationCount(): void
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

            public function setCommonFactoryClass(string $commonFactoryClass): void
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

    public function testReturnsMasterFactoryInstance(): void
    {
        $this->assertInstanceOf(MasterFactory::class, (CliFactoryBootstrap::createMasterFactory()));
    }

    public function testRegistersAnySpecifiedFactories(): void
    {
        $spyFactory = $this->createSpyFactory();
        CliFactoryBootstrap::createMasterFactory($spyFactory);

        $this->assertTrue($spyFactory->wasRegistered);
    }

    public function testRegistersCommonFactoryWithoutItBeingSpecified(): void
    {
        $testCliBootstrap = $this->createTestCliFactoryBootstrap();

        $spyCommonFactory = $this->createSpyCommonFactory();
        $testCliBootstrap->setCommonFactoryClass(get_class($spyCommonFactory));

        $testCliBootstrap->createMasterFactory();

        $this->assertSame(1, $spyCommonFactory->getRegistrationCount());
    }

    public function testDoesNotRegisterDefaultFactoryIfAlsoSpecifiedAsArgument(): void
    {
        $testCliBootstrap = $this->createTestCliFactoryBootstrap();

        $spyCommonFactory = $this->createSpyCommonFactory();
        $testCliBootstrap->setCommonFactoryClass(get_class($spyCommonFactory));

        $testCliBootstrap->createMasterFactory($spyCommonFactory);

        $this->assertSame(1, $spyCommonFactory->getRegistrationCount());
    }
}

