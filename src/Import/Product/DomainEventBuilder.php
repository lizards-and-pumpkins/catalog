<?php
namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

interface DomainEventBuilder
{
    /**
     * @param Message $message
     * @return DomainEvent
     */
    public function fromMessage(Message $message);
}
