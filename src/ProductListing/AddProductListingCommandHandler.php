<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class AddProductListingCommandHandler implements CommandHandler
{
    /**
     * @var DomainEventQueue
     */
    private $eventQueue;

    public function __construct(DomainEventQueue $domainEventQueue)
    {
        $this->eventQueue = $domainEventQueue;
    }
    
    public function process(Message $message): void
    {
        $command = AddProductListingCommand::fromMessage($message);
        $this->eventQueue->add(new ProductListingWasAddedDomainEvent($command->getProductListing()));
    }
}
