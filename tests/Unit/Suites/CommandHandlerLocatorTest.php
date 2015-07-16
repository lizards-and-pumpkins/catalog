<?php

namespace Brera;

use Brera\Product\ProductId;
use Brera\Product\ProductStockQuantitySource;
use Brera\Product\UpdateMultipleProductStockQuantityCommand;
use Brera\Product\UpdateMultipleProductStockQuantityCommandHandler;
use Brera\Product\UpdateProductStockQuantityCommandHandler;
use Brera\Product\UpdateProductStockQuantityCommand;

/**
 * @covers \Brera\CommandHandlerLocator
 * @uses   \Brera\Product\UpdateProductStockQuantityCommand
 * @uses   \Brera\Product\UpdateMultipleProductStockQuantityCommand
 */
class CommandHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandHandlerLocator
     */
    private $locator;

    /**
     * @var CommonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->getMock(CommonFactory::class);
        $this->locator = new CommandHandlerLocator($this->factory);
    }

    public function testExceptionIsThrownIfNoHandlerIsLocated()
    {
        $stubCommand = $this->getMock(Command::class);
        $this->setExpectedException(UnableToFindCommandHandlerException::class);
        $this->locator->getHandlerFor($stubCommand);
    }

    public function testUpdateProductStockQuantityCommandHandlerIsLocatedAndReturned()
    {
        $stubHandler = $this->getMock(UpdateProductStockQuantityCommandHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createUpdateProductStockQuantityCommandHandler')
            ->willReturn($stubHandler);

        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $productImportCommand = new UpdateProductStockQuantityCommand($stubProductId, $stubProductStockQuantitySource);

        $result = $this->locator->getHandlerFor($productImportCommand);

        $this->assertInstanceOf(UpdateProductStockQuantityCommandHandler::class, $result);
    }

    public function testUpdateMultipleProductStockQuantityCommandHandlerIsLocatedAndReturned()
    {
        $stubHandler = $this->getMock(UpdateMultipleProductStockQuantityCommandHandler::class, [], [], '', false);
        $stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createUpdateMultipleProductStockQuantityCommandHandler')
            ->willReturn($stubHandler);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $productImportCommand = new UpdateMultipleProductStockQuantityCommand([$stubProductStockQuantitySource]);

        $result = $this->locator->getHandlerFor($productImportCommand);

        $this->assertInstanceOf(UpdateMultipleProductStockQuantityCommandHandler::class, $result);
    }
}
