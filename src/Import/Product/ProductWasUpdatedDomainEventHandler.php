<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Exception\NoProductWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ProductWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var Message
     */
    private $event;

    /**
     * @var ProductProjector
     */
    private $projector;

    public function __construct(Message $event, ProductProjector $projector)
    {
        if ($event->getName() !== 'product_was_updated_domain_event') {
            $message = sprintf('Expected "product_was_updated" domain event, got "%s"', $event->getName());
            throw new NoProductWasUpdatedDomainEventMessageException($message);
        }
        $this->event = $event;
        $this->projector = $projector;
    }

    public function process()
    {
        $payload = json_decode($this->event->getPayload(), true);
        
        // todo: encapsulate product serialization and rehydration
        $product = $payload['product'][Product::TYPE_KEY] === ConfigurableProduct::TYPE_CODE ?
            ConfigurableProduct::fromArray($payload['product']) :
            SimpleProduct::fromArray($payload['product']);
        
        $this->projector->project($product);
    }
}
