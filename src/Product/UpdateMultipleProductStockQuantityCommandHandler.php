<?php

namespace Brera\Product;

use Brera\CommandHandler;
use Brera\Queue\Queue;

class UpdateMultipleProductStockQuantityCommandHandler implements CommandHandler
{
    /**
     * @var UpdateMultipleProductStockQuantityCommand
     */
    private $command;

    /**
     * @var Queue
     */
    private $commandQueue;

    public function __construct(UpdateMultipleProductStockQuantityCommand $command, Queue $commandQueue)
    {
        $this->command = $command;
        $this->commandQueue = $commandQueue;
    }

    public function process()
    {
        foreach ($this->command->getProductStockQuantitySourceArray() as $productStockQuantitySource) {
            $productId = $productStockQuantitySource->getProductId();
            $this->commandQueue->add(new UpdateProductStockQuantityCommand($productId, $productStockQuantitySource));
        }
    }
}
