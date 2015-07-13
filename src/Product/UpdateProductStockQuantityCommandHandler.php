<?php

namespace Brera\Product;

use Brera\CommandHandler;
use Brera\Queue\Queue;

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

    public function __construct(UpdateProductStockQuantityCommand $command, Queue $domainEventQueue) {
        $this->command = $command;
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $payload = $this->command->getPayload();
        $command = new ProductStockQuantityUpdatedDomainEvent($payload);

        $this->domainEventQueue->add($command);
    }
}
