<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\CommandHandler;
use LizardsAndPumpkins\Queue\Queue;

/**
 * @covers \LizardsAndPumpkins\Product\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent
 */
class AddProductListingCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var AddProductListingCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        /**
         * @var ProductListingCriteria|\PHPUnit_Framework_MockObject_MockObject $stubProductListingCriteria
         */
        $stubProductListingCriteria = $this->getMock(ProductListingCriteria::class, [], [], '', false);

        /** @var AddProductListingCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(AddProductListingCommand::class, [], [], '', false);
        $stubCommand->method('getProductListingCriteria')->willReturn($stubProductListingCriteria);

        $this->mockDomainEventQueue = $this->getMock(Queue::class);

        $this->commandHandler = new AddProductListingCommandHandler($stubCommand, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testProductListingWasAddedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add')
            ->with($this->isInstanceOf(ProductListingWasAddedDomainEvent::class));

        $this->commandHandler->process();
    }
}
