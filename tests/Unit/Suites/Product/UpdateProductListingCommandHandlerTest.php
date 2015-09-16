<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\CommandHandler;
use LizardsAndPumpkins\Queue\Queue;

/**
 * @covers \LizardsAndPumpkins\Product\UpdateProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\Product\ProductListingWasUpdatedDomainEvent
 */
class UpdateProductListingCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var UpdateProductListingCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        /**
         * @var ProductListingMetaInfo|\PHPUnit_Framework_MockObject_MockObject $stubProductListingMetaInfoSource
         */
        $stubProductListingMetaInfoSource = $this->getMock(ProductListingMetaInfo::class, [], [], '', false);
        $stubProductListingMetaInfoSource->method('getUrlKey')->willReturn('foo');

        /** @var UpdateProductListingCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(UpdateProductListingCommand::class, [], [], '', false);
        $stubCommand->method('getProductListingMetaInfoSource')->willReturn($stubProductListingMetaInfoSource);

        $this->mockDomainEventQueue = $this->getMock(Queue::class);

        $this->commandHandler = new UpdateProductListingCommandHandler($stubCommand, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testProductListingWasUpdatedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add')
            ->with($this->isInstanceOf(ProductListingWasUpdatedDomainEvent::class));

        $this->commandHandler->process();
    }
}
