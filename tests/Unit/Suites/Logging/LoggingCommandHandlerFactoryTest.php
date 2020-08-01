<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Command\CommandHandlerFactory;
use LizardsAndPumpkins\Messaging\Command\Logging\ProcessTimeLoggingCommandHandlerDecorator;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Core\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\UnitTestFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Logging\LoggingCommandHandlerFactory
 * @uses   \LizardsAndPumpkins\Core\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Core\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommandHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommandHandler
 * @uses   \LizardsAndPumpkins\Import\ImportCatalogCommandHandler
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommandHandler
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler
 * @uses   \LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope
 * @uses   \LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\Messaging\Command\Logging\ProcessTimeLoggingCommandHandlerDecorator
 */
class LoggingCommandHandlerFactoryTest extends TestCase
{
    /**
     * @var LoggingCommandHandlerFactory
     */
    private $loggingCommandHandlerFactory;

    protected function setUp()
    {
        $masterFactory = new CatalogMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);
        $masterFactory->register(new UnitTestFactory($this));
        $this->loggingCommandHandlerFactory = new LoggingCommandHandlerFactory($masterFactory);
        $masterFactory->register($this->loggingCommandHandlerFactory);
    }

    public function testItImplementsTheCommandFactoryInterfaceAndFactoryInterface()
    {
        $this->assertInstanceOf(CommandHandlerFactory::class, $this->loggingCommandHandlerFactory);
        $this->assertInstanceOf(Factory::class, $this->loggingCommandHandlerFactory);
    }

    public function testItReturnsADecoratedUpdateContentBlockCommandHandler()
    {
        $commandHandler = $this->loggingCommandHandlerFactory->createUpdateContentBlockCommandHandler();
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedUpdateProductCommandHandler()
    {
        $commandHandler = $this->loggingCommandHandlerFactory->createUpdateProductCommandHandler();
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedAddProductListingCommandHandler()
    {
        $commandHandler = $this->loggingCommandHandlerFactory->createAddProductListingCommandHandler();
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedAddProductImageCommandHandler()
    {
        $commandHandler = $this->loggingCommandHandlerFactory->createAddImageCommandHandler();
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testReturnsADecoratedShutdownWorkerDirectiveHandler()
    {
        $commandHandler = $this->loggingCommandHandlerFactory->createShutdownWorkerCommandHandler();
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testReturnsADecoratedImportCatalogCommandHandler()
    {
        $commandHandler = $this->loggingCommandHandlerFactory->createImportCatalogCommandHandler();
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testReturnsADecoratedSetCurrentDataVersionCommandHandler()
    {
        $commandHandler = $this->loggingCommandHandlerFactory->createSetCurrentDataVersionCommandHandler();
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testReturnsADecoratedUpdateTemplateCommandHandler()
    {
        $commandHandler = $this->loggingCommandHandlerFactory->createUpdateTemplateCommandHandler();
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }
}
