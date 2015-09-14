<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\CommandHandler;
use LizardsAndPumpkins\Queue\Queue;

class UpdateProductStockQuantityCommandHandler implements CommandHandler
{
    /**
     * @var UpdateProductStockQuantityCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(UpdateProductStockQuantityCommand $command, Queue $domainEventQueue)
    {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $productStockQuantitySource = $this->command->getProductStockQuantitySource();
        $productId = $productStockQuantitySource->getProductId();

        $event = new ProductStockQuantityWasUpdatedDomainEvent($productId, $productStockQuantitySource);

        $this->domainEventQueue->add($event);
    }
}
