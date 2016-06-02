<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Exception\NoAddProductListingCommandMessageException;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

/**
 * @covers \LizardsAndPumpkins\ProductListing\AddProductListingCommandHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListing
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommand
 */
class AddProductListingCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var AddProductListingCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        /**
         * @var ProductListing|\PHPUnit_Framework_MockObject_MockObject $stubProductListing
         */
        $stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $stubProductListing->method('getContextData')->willReturn([DataVersion::CONTEXT_CODE => '123']);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(Message::class, [], [], '', false);
        $stubCommand->method('getPayload')->willReturn(json_encode(['listing' => serialize($stubProductListing)]));
        $stubCommand->method('getName')->willReturn('add_product_listing');

        $this->mockDomainEventQueue = $this->getMock(DomainEventQueue::class, [], [], '', false);

        $this->commandHandler = new AddProductListingCommandHandler($stubCommand, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testProductListingWasAddedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('addVersioned')
            ->with('product_listing_was_added', $this->isType('string'), $this->anything());

        $this->commandHandler->process();
    }
}
