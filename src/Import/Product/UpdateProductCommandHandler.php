<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateProductCommandHandler implements CommandHandler
{
    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(DomainEventQueue $domainEventQueue)
    {
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process(Message $message)
    {
        $product = UpdateProductCommand::fromMessage($message)->getProduct();
        $this->domainEventQueue->add(new ProductWasUpdatedDomainEvent($product));
    }
}
