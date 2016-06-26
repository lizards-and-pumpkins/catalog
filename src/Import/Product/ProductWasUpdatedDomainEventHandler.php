<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ProductWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var Message
     */
    private $message;

    /**
     * @var ProductProjector
     */
    private $projector;

    /**
     * @var ProductWasUpdatedDomainEventBuilder
     */
    private $domainEventBuilder;

    public function __construct(
        Message $message,
        ProductProjector $projector,
        ProductWasUpdatedDomainEventBuilder $domainEventBuilder
    ) {
        $this->message = $message;
        $this->projector = $projector;
        $this->domainEventBuilder = $domainEventBuilder;
    }

    public function process()
    {
        $product = $this->domainEventBuilder->fromMessage($this->message)->getProduct();
        $this->projector->project($product);
    }
}
