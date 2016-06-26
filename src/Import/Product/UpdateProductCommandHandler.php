<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateProductCommandHandler implements CommandHandler
{
    /**
     * @var Message
     */
    private $message;

    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    /**
     * @var UpdateProductCommandBuilder
     */
    private $commandBuilder;

    public function __construct(
        Message $message,
        DomainEventQueue $domainEventQueue,
        UpdateProductCommandBuilder $commandBuilder
    ) {
        $this->message = $message;
        $this->domainEventQueue = $domainEventQueue;
        $this->commandBuilder = $commandBuilder;
    }

    public function process()
    {
        $product = $this->commandBuilder->fromMessage($this->message)->getProduct();
        $this->domainEventQueue->add(new ProductWasUpdatedDomainEvent($product));
    }
}
