<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\Command\TestStubConsoleCommand;
use LizardsAndPumpkins\ConsoleCommand\Exception\NoConsoleCommandSpecifiedException;
use LizardsAndPumpkins\ConsoleCommand\TestDouble\MockCliCommand;
use LizardsAndPumpkins\Core\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Core\Factory\FactoryWithCallbackTrait;
use LizardsAndPumpkins\Messaging\Queue\Logging\LoggingQueueDecorator;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\CliBootstrap
 * @uses   \LizardsAndPumpkins\ConsoleCommand\CliFactoryBootstrap
 * @uses   \LizardsAndPumpkins\ConsoleCommand\NameToClassConvertingConsoleCommandLocator
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
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Logging\LoggingQueueDecorator
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
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\ConsoleCommand\ConsoleCommandFactory
 * @uses   \LizardsAndPumpkins\Core\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Core\Factory\FactoryWithCallbackTrait
 * @uses   \LizardsAndPumpkins\Core\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class CliBootstrapTest extends TestCase
{
    final protected function tearDown(): void
    {
        unset($_SERVER[CliBootstrap::ENV_DEBUG_VAR], $_ENV[CliBootstrap::ENV_DEBUG_VAR]);
    }

    public function testReturnsAnInstanceOfTheSpecifiedCommand(): void
    {
        $command = CliBootstrap::create(MockCliCommand::class);
        $this->assertInstanceOf(MockCliCommand::class, $command);
    }

    public function testInjectsMasterFactoryAndCLIMateInstanceToCommand(): void
    {
        /** @var MockCliCommand $command */
        $command = CliBootstrap::create(MockCliCommand::class);
        $this->assertInstanceOf(MasterFactory::class, $command->factory);
        $this->assertInstanceOf(CLImate::class, $command->cliMate);
    }

    public function testRegistersSpecifiedFactoriesWithMasterFactory(): void
    {
        $spyFactory = new class implements FactoryWithCallback
        {
            use FactoryWithCallbackTrait;

            public $wasRegistered = false;

            public function factoryRegistrationCallback(MasterFactory $masterFactory): void
            {
                $this->wasRegistered = true;
            }
        };
        /** @var MockCliCommand $command */
        CliBootstrap::create(MockCliCommand::class, $spyFactory);

        $this->assertTrue($spyFactory->wasRegistered);
    }

    public function testDoesNotRegisterLoggingFactoriesIfDebugEnvironmentVariableIsNotSet(): void
    {
        unset($_SERVER[CliBootstrap::ENV_DEBUG_VAR], $_ENV[CliBootstrap::ENV_DEBUG_VAR]);

        /** @var MockCliCommand $command */
        $command = CliBootstrap::create(MockCliCommand::class, new UnitTestFactory($this));

        $this->assertNotInstanceOf(LoggingQueueDecorator::class, $command->factory->createEventMessageQueue());
    }

    public function testThrowsExceptionIfTheArgumentVectorContainsNoName(): void
    {
        $this->expectException(NoConsoleCommandSpecifiedException::class);
        $this->expectExceptionMessage('No command name specified.');

        CliBootstrap::fromArgumentsVector(['foo']);
    }

    public function testReturnsInstanceOfTheSpecifiedCommand(): void
    {
        $consoleCommand = CliBootstrap::fromArgumentsVector(['foo script', 'test:stub'], new UnitTestFactory($this));
        $this->assertInstanceOf(TestStubConsoleCommand::class, $consoleCommand);
    }
}
