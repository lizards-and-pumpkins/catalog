<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\CommandHandler;
use LizardsAndPumpkins\Queue\Queue;

class UpdateProductCommandHandler implements CommandHandler
{
    /**
     * @var UpdateProductCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $domainEventQueue;

    public function __construct(UpdateProductCommand $command, Queue $domainEventQueue)
    {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $productSource = $this->command->getProductSource();
        $productId = $productSource->getId();

        $this->domainEventQueue->add(new ProductWasUpdatedDomainEvent($productId, $productSource));
    }
}
