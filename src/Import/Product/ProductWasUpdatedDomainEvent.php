<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Exception\NoProductWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ProductWasUpdatedDomainEvent implements DomainEvent
{
    const CODE = 'product_was_updated';
    
    /**
     * @var Product
     */
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return Message
     */
    public function toMessage()
    {
        $payload = ['id' => (string) $this->product->getId(), 'product' => json_encode($this->product)];
        return Message::withCurrentTime(self::CODE, $payload, []);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        // TODO: Remove from interface
    }

    /**
     * @return DataVersion
     */
    public function getDataVersion()
    {
        return DataVersion::fromVersionString($this->product->getContext()->getValue(DataVersion::CONTEXT_CODE));
    }

    /**
     * @param Message $message
     * @param ProductAvailability $productAvailability
     * @return ConfigurableProduct|SimpleProduct
     */
    public static function rehydrateProduct(Message $message, ProductAvailability $productAvailability)
    {
        // todo: encapsulate product serialization and rehydration

        self::validateMessageType($message);

        $productData = json_decode($message->getPayload()['product'], true);

        if ($productData[Product::TYPE_KEY] === ConfigurableProduct::TYPE_CODE) {
            return ConfigurableProduct::fromArray($productData, $productAvailability);
        }

        return SimpleProduct::fromArray($productData);
    }

    /**
     * @param Message $message
     */
    private static function validateMessageType(Message $message)
    {
        if ($message->getName() !== ProductWasUpdatedDomainEvent::CODE) {
            throw new NoProductWasUpdatedDomainEventMessageException(
                sprintf('Expected "%s" domain event, got "%s"', ProductWasUpdatedDomainEvent::CODE, $message->getName())
            );
        }
    }
}
