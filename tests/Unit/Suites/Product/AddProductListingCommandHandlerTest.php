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
         * @var ProductListingMetaInfo|\PHPUnit_Framework_MockObject_MockObject $stubProductListingMetaInfo
         */
        $stubProductListingMetaInfo = $this->getMock(ProductListingMetaInfo::class, [], [], '', false);
        $stubProductListingMetaInfo->method('getUrlKey')->willReturn('foo');

        /** @var AddProductListingCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(AddProductListingCommand::class, [], [], '', false);
        $stubCommand->method('getProductListingMetaInfo')->willReturn($stubProductListingMetaInfo);

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
