<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateProductCommandHandler implements CommandHandler
{
    /**
     * @var UpdateProductCommand
     */
    private $command;

    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(Message $message, DomainEventQueue $domainEventQueue)
    {
        $this->command = UpdateProductCommand::fromMessage($message);
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $product = $this->command->getProduct();
        $this->domainEventQueue->add(new ProductWasUpdatedDomainEvent($product));
    }
}
