<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\Command\TestStubConsoleCommand;
use LizardsAndPumpkins\ConsoleCommand\Exception\NoConsoleCommandSpecifiedException;
use LizardsAndPumpkins\ConsoleCommand\TestDouble\MockCliCommand;
use LizardsAndPumpkins\Logging\LoggingQueueDecorator;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallbackTrait;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
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
 * @uses   \LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommandHandler
 * @uses   \LizardsAndPumpkins\Import\SnippetRendererCollection
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Logging\LoggingCommandHandlerFactory
 * @uses   \LizardsAndPumpkins\Logging\LoggingDomainEventHandlerFactory
 * @uses   \LizardsAndPumpkins\Logging\LoggingQueueDecorator
 * @uses   \LizardsAndPumpkins\Logging\LoggingQueueFactory
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler
 * @uses   \LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope
 * @uses   \LizardsAndPumpkins\ProductDetail\Import\ConfigurableProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductCanonicalTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingDescriptionSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetProjector
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateProjector
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingCanonicalTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\ConsoleCommand\ConsoleCommandFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryWithCallbackTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class CliBootstrapTest extends TestCase
{
    protected function tearDown()
    {
        unset($_SERVER[CliBootstrap::ENV_DEBUG_VAR], $_ENV[CliBootstrap::ENV_DEBUG_VAR]);
    }

    public function testReturnsAnInstanceOfTheSpecifiedCommand()
    {
        $command = CliBootstrap::create(MockCliCommand::class);
        $this->assertInstanceOf(MockCliCommand::class, $command);
    }

    public function testInjectsMasterFactoryAndCLIMateInstanceToCommand()
    {
        /** @var MockCliCommand $command */
        $command = CliBootstrap::create(MockCliCommand::class);
        $this->assertInstanceOf(MasterFactory::class, $command->factory);
        $this->assertInstanceOf(CLImate::class, $command->cliMate);
    }

    public function testRegistersSpecifiedFactoriesWithMasterFactory()
    {
        $spyFactory = new class implements FactoryWithCallback
        {
            use FactoryWithCallbackTrait;

            public $wasRegistered = false;

            public function factoryRegistrationCallback(MasterFactory $masterFactory)
            {
                $this->wasRegistered = true;
            }
        };
        /** @var MockCliCommand $command */
        CliBootstrap::create(MockCliCommand::class, $spyFactory);

        $this->assertTrue($spyFactory->wasRegistered);
    }

    public function testDoesNotRegisterLoggingFactoriesIfDebugEnvironmentVariableIsNotSet()
    {
        unset($_SERVER[CliBootstrap::ENV_DEBUG_VAR], $_ENV[CliBootstrap::ENV_DEBUG_VAR]);

        /** @var MockCliCommand $command */
        $command = CliBootstrap::create(MockCliCommand::class, new UnitTestFactory($this));

        $this->assertNotInstanceOf(LoggingQueueDecorator::class, $command->factory->createEventMessageQueue());
    }

    public function testRegistersLoggingFactoriesIfDebugServerEnvironmentVariableIsSet()
    {
        $_SERVER[CliBootstrap::ENV_DEBUG_VAR] = 1;

        /** @var MockCliCommand $command */
        $command = CliBootstrap::create(MockCliCommand::class, new UnitTestFactory($this));

        $this->assertInstanceOf(LoggingQueueDecorator::class, $command->factory->createEventMessageQueue());
    }

    public function testRegistersLoggingFactoriesIfDebugEnvEnvironmentVariableIsSet()
    {
        $_ENV[CliBootstrap::ENV_DEBUG_VAR] = 1;

        /** @var MockCliCommand $command */
        $command = CliBootstrap::create(MockCliCommand::class, new UnitTestFactory($this));

        $this->assertInstanceOf(LoggingQueueDecorator::class, $command->factory->createEventMessageQueue());
    }

    public function testThrowsExceptionIfTheArgumentVectorContainsNoName()
    {
        $this->expectException(NoConsoleCommandSpecifiedException::class);
        $this->expectExceptionMessage('No command name specified.');

        CliBootstrap::fromArgumentsVector(['foo']);
    }

    public function testReturnsInstanceOfTheSpecifiedCommand()
    {
        $consoleCommand = CliBootstrap::fromArgumentsVector(['foo script', 'test:stub'], new UnitTestFactory($this));
        $this->assertInstanceOf(TestStubConsoleCommand::class, $consoleCommand);
    }
}
