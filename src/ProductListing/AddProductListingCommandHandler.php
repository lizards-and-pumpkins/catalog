<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

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
        $this->eventQueue->add(new ProductListingWasAddedDomainEvent($this->command->getProductListing()));
    }
}
