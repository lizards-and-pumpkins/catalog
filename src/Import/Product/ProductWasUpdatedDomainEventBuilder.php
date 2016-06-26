<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Exception\NoProductWasUpdatedDomainEventMessageException;
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

    public function fromMessage(Message $message)
    {
        if ($message->getName() !== ProductWasUpdatedDomainEvent::CODE) {
            throw new NoProductWasUpdatedDomainEventMessageException(
                sprintf('Expected "%s" domain event, got "%s"', ProductWasUpdatedDomainEvent::CODE, $message->getName())
            );
        }
        $productData = json_decode($message->getPayload()['product'], true);
        
        return new ProductWasUpdatedDomainEvent($this->rehydrateProduct($productData));
    }

    /**
     * @param mixed[] $productData
     * @return ConfigurableProduct|SimpleProduct
     */
    private function rehydrateProduct(array $productData)
    {
        // todo: encapsulate product serialization and rehydration
        
        if ($productData[Product::TYPE_KEY] === ConfigurableProduct::TYPE_CODE) {
            return ConfigurableProduct::fromArray($productData, $this->productAvailability);
        }
        
        return SimpleProduct::fromArray($productData);
    }
}
