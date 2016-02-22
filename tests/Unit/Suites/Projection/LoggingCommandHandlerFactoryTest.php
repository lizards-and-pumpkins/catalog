<?php

namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\CommandFactory;
use LizardsAndPumpkins\CommonFactory;
use LizardsAndPumpkins\Content\UpdateContentBlockCommand;
use LizardsAndPumpkins\Factory;
use LizardsAndPumpkins\Image\AddImageCommand;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\UpdateProductCommand;
use LizardsAndPumpkins\SampleMasterFactory;
use LizardsAndPumpkins\UnitTestFactory;

/**
 * @covers \LizardsAndPumpkins\Projection\LoggingCommandHandlerFactory
 * @uses   \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\CommonFactory
 * @uses   \LizardsAndPumpkins\Content\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Projection\ProcessTimeLoggingCommandHandlerDecorator
 * @uses   \LizardsAndPumpkins\Product\UpdateProductCommandHandler
 * @uses   \LizardsAndPumpkins\Product\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\Image\AddImageCommandHandler
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
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new UnitTestFactory());
        $this->loggingCommandHandlerFactory = new LoggingCommandHandlerFactory();
        $masterFactory->register($this->loggingCommandHandlerFactory);
    }

    public function testItImplementsTheCommandFactoryInterfaceAndFactoryInterface()
    {
        $this->assertInstanceOf(CommandFactory::class, $this->loggingCommandHandlerFactory);
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
