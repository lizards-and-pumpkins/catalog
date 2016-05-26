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
     * @var Message
     */
    private $command;

    /**
     * @var DomainEventQueue
     */
    private $eventQueue;

    public function __construct(Message $command, DomainEventQueue $domainEventQueue)
    {
        if ('add_product_listing_command' !== $command->getName()) {
            $message = sprintf('Expected "add_product_listing" command, got "%s"', $command->getName());
            throw new NoAddProductListingCommandMessageException($message);
        }
        $this->command = $command;
        $this->eventQueue = $domainEventQueue;
    }

    public function process()
    {
        $commandPayload = json_decode($this->command->getPayload(), true);
        $productListing = ProductListing::rehydrate($commandPayload['listing']);
        $eventPayload = json_encode(['listing' => $productListing->serialize()]);
        $version = DataVersion::fromVersionString($productListing->getContextData()[ContextVersion::CODE]);
        $this->eventQueue->addVersioned('product_listing_was_added', $eventPayload, $version);
    }
}
