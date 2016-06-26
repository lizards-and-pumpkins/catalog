<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
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
}
