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
     * @var ProductDTO
     */
    private $product;

    public function __construct(ProductDTO $product)
    {
        $this->product = $product;
    }

    /**
     * @return ProductDTO
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
        if ($message->getName() !== self::CODE) {
            throw new NoProductWasUpdatedDomainEventMessageException(
                sprintf('Expected "%s" domain event, got "%s"', self::CODE, $message->getName())
            );
        }
        $productData = json_decode($message->getPayload()['product'], true);
        return new self(self::rehydrateProduct($productData));
    }

    /**
     * @param mixed[] $productData
     * @return ConfigurableProduct|SimpleProduct
     */
    private static function rehydrateProduct(array $productData)
    {
        // todo: encapsulate product serialization and rehydration
        $product = $productData[ProductDTO::TYPE_KEY] === ConfigurableProduct::TYPE_CODE ?
            ConfigurableProduct::fromArray($productData) :
            SimpleProduct::fromArray($productData);
        return $product;
    }

    /**
     * @return DataVersion
     */
    public function getDataVersion()
    {
        return DataVersion::fromVersionString($this->product->getContext()->getValue(DataVersion::CONTEXT_CODE));
    }
}
