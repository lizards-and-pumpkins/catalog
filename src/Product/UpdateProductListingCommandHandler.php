<?php

namespace Brera\Product;

use Brera\CommandHandler;
use Brera\Queue\Queue;

class UpdateProductListingCommandHandler implements CommandHandler
{
    /**
     * @var UpdateProductListingCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(UpdateProductListingCommand $command, Queue $domainEventQueue)
    {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $productListingSource = $this->command->getProductListingSource();
        $urlKey = $productListingSource->getUrlKey();

        $this->domainEventQueue->add(new ProductListingWasUpdatedDomainEvent($urlKey, $productListingSource));
    }
}
