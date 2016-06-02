<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Context\DataVersion\ContextVersion;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Exception\NoAddProductListingCommandMessageException;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

class AddProductListingCommandHandler implements CommandHandler
{
    /**
     * @var AddProductListingCommand
     */
    private $command;

    /**
     * @var DomainEventQueue
     */
    private $eventQueue;

    public function __construct(Message $message, DomainEventQueue $domainEventQueue)
    {
        $this->command = AddProductListingCommand::fromMessage($message);
        $this->eventQueue = $domainEventQueue;
    }
    
    public function process()
    {
        $productListing = $this->command->getProductListing();
        $eventPayload = json_encode(['listing' => $productListing->serialize()]);
        $version = DataVersion::fromVersionString($productListing->getContextData()[DataVersion::CONTEXT_CODE]);
        $this->eventQueue->addVersioned('product_listing_was_added', $eventPayload, $version);
    }
}
