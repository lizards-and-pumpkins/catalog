<?php

namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\Logging\LoggingCommandHandlerFactory;
use LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Import\Image\AddImageCommand;
use LizardsAndPumpkins\ProductListing\AddProductListingCommand;
use LizardsAndPumpkins\Import\Product\UpdateProductCommand;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;
use LizardsAndPumpkins\UnitTestFactory;

/**
 * @covers \LizardsAndPumpkins\Logging\LoggingCommandHandlerFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Logging\ProcessTimeLoggingCommandHandlerDecorator
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommandHandler
 */
class LoggingCommandHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggingCommandHandlerFactory
     */
    private $loggingCommandHandlerFactory;

    protected function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $commonFactory = new CommonFactory();
        $masterFactory->register($commonFactory);
        $masterFactory->register(new UnitTestFactory());
        $this->loggingCommandHandlerFactory = new LoggingCommandHandlerFactory($commonFactory);
        $masterFactory->register($this->loggingCommandHandlerFactory);
    }

    public function testItImplementsTheCommandFactoryInterfaceAndFactoryInterface()
    {
        $this->assertInstanceOf(CommandHandlerFactory::class, $this->loggingCommandHandlerFactory);
        $this->assertInstanceOf(Factory::class, $this->loggingCommandHandlerFactory);
    }

    public function testItReturnsADecoratedUpdateContentBlockCommandHandler()
    {
        $stubCommand = $this->getMock(UpdateContentBlockCommand::class, [], [], '', false);
        $commandHandler = $this->loggingCommandHandlerFactory->createUpdateContentBlockCommandHandler($stubCommand);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedUpdateProductCommandHandler()
    {
        $stubCommand = $this->getMock(UpdateProductCommand::class, [], [], '', false);
        $commandHandler = $this->loggingCommandHandlerFactory->createUpdateProductCommandHandler($stubCommand);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedAddProductListingCommandHandler()
    {
        $stubCommand = $this->getMock(AddProductListingCommand::class, [], [], '', false);
        $commandHandler = $this->loggingCommandHandlerFactory->createAddProductListingCommandHandler($stubCommand);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedAddProductImageCommandHandler()
    {
        $stubCommand = $this->getMock(AddImageCommand::class, [], [], '', false);
        $commandHandler = $this->loggingCommandHandlerFactory->createAddImageCommandHandler($stubCommand);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }
}
