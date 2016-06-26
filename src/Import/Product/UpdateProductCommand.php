<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateProductCommand implements Command
{
    const CODE = 'update_product';

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
        $name = self::CODE;
        $payload = ['id' => (string)$this->product->getId(), 'product' => json_encode($this->product)];
        $metadata = ['data_version' => (string) $this->getDataVersion()];
        return Message::withCurrentTime($name, $payload, $metadata);
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
