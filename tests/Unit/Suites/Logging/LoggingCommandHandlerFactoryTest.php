<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Command\CommandHandlerFactory;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
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

    /**
     * @return Message|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createStubCommand(string $name): Message
    {
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(Message::class, [], [], '', false);
        $stubCommand->method('getName')->willReturn($name);
        return $stubCommand;
    }

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
        $stubCommand = $this->createStubCommand('update_content_block_command');
        $commandHandler = $this->loggingCommandHandlerFactory->createUpdateContentBlockCommandHandler($stubCommand);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedUpdateProductCommandHandler()
    {
        $stubCommand = $this->createStubCommand('update_product_command');
        $commandHandler = $this->loggingCommandHandlerFactory->createUpdateProductCommandHandler($stubCommand);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedAddProductListingCommandHandler()
    {
        $stubCommand = $this->createStubCommand('add_product_listing_command');
        $commandHandler = $this->loggingCommandHandlerFactory->createAddProductListingCommandHandler($stubCommand);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }

    public function testItReturnsADecoratedAddProductImageCommandHandler()
    {
        $stubCommand = $this->createStubCommand('add_image_command');
        $commandHandler = $this->loggingCommandHandlerFactory->createAddImageCommandHandler($stubCommand);
        $this->assertInstanceOf(ProcessTimeLoggingCommandHandlerDecorator::class, $commandHandler);
    }
}
