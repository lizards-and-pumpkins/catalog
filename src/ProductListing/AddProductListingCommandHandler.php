<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Queue;

class AddProductListingCommandHandler implements CommandHandler
{
    /**
     * @var AddProductListingCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(AddProductListingCommand $command, Queue $domainEventQueue)
    {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $productListing = $this->command->getProductListing();
        $this->domainEventQueue->add(new ProductListingWasAddedDomainEvent($productListing));
    }
}
