<?php

namespace Brera\Product;

use Brera\CommandHandler;
use Brera\Queue\Queue;

/**
 * @covers \Brera\Product\UpdateMultipleProductStockQuantityCommandHandler
 * @uses   \Brera\Product\UpdateMultipleProductStockQuantityCommand
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
        $xml = file_get_contents(__DIR__ . '/../../../shared-fixture/stock.xml');

        $this->mockCommand = $this->getMock(UpdateMultipleProductStockQuantityCommand::class, [], [], '', false);
        $this->mockCommand->method('getPayload')->willReturn($xml);

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
