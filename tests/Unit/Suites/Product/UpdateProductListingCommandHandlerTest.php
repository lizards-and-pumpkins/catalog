<?php

namespace Brera\Product;

use Brera\CommandHandler;
use Brera\Queue\Queue;

/**
 * @covers \Brera\Product\UpdateProductListingCommandHandler
 * @uses   \Brera\Product\ProductListingWasUpdatedDomainEvent
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
        /** @var ProductListingSource|\PHPUnit_Framework_MockObject_MockObject $stubProductListingSource */
        $stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);
        $stubProductListingSource->method('getUrlKey')->willReturn('foo');

        /** @var UpdateProductListingCommand|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(UpdateProductListingCommand::class, [], [], '', false);
        $stubCommand->method('getProductListingSource')->willReturn($stubProductListingSource);

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
