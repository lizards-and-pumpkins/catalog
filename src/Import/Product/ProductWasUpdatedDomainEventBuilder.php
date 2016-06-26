<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Queue\Message;

class ProductWasUpdatedDomainEventBuilder
{
    /**
     * @var ProductAvailability
     */
    private $productAvailability;

    public function __construct(ProductAvailability $productAvailability)
    {
        $this->productAvailability = $productAvailability;
    }

    /**
     * @param Message $message
     * @return ProductWasUpdatedDomainEvent
     */
    public function fromMessage(Message $message)
    {
        $product = ProductWasUpdatedDomainEvent::rehydrateProduct($message, $this->productAvailability);
        return new ProductWasUpdatedDomainEvent($product);
    }
}
