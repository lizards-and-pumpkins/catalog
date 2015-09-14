<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\CommandHandler;
use LizardsAndPumpkins\Queue\Queue;

/**
 * @covers \LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommandHandler
 * @uses   \LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommand
 * @uses   \LizardsAndPumpkins\Product\UpdateProductStockQuantityCommand
 */
class UpdateMultipleProductStockQuantityCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateMultipleProductStockQuantityCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommand;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var UpdateMultipleProductStockQuantityCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        $stubProductStockQuantitySource1 = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);
        $stubProductStockQuantitySource2 = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);
        $stubProductStockQuantitySourceArray = [$stubProductStockQuantitySource1, $stubProductStockQuantitySource2];

        $this->mockCommand = $this->getMock(UpdateMultipleProductStockQuantityCommand::class, [], [], '', false);
        $this->mockCommand->method('getProductStockQuantitySourceArray')
            ->willReturn($stubProductStockQuantitySourceArray);

        $this->mockCommandQueue = $this->getMock(Queue::class);

        $this->commandHandler = new UpdateMultipleProductStockQuantityCommandHandler(
            $this->mockCommand,
            $this->mockCommandQueue
        );
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testDomainEventCommandIsPutIntoCommandQueue()
    {
        $this->mockCommandQueue->expects($this->exactly(2))
            ->method('add')
            ->with($this->isInstanceOf(UpdateProductStockQuantityCommand::class));
        $this->commandHandler->process();
    }
}
