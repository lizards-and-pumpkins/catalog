<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Exception\NoUpdateProductCommandMessageException;
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
        $payload = ['id' => (string)$this->product->getId(), 'product' => $this->product];
        $metadata = ['data_version' => (string) $this->getDataVersion()];
        return Message::withCurrentTime($name, json_encode($payload), $metadata);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        if ($message->getName() !== 'update_product') {
            throw self::createInvalidMessageException($message->getName());
        }
        
        $payload = json_decode($message->getPayload(), true);
        $product = self::rehydrateProduct($payload['product']);
        return new self($product);
    }

    /**
     * @param string $messageName
     * @return NoUpdateProductCommandMessageException
     */
    private static function createInvalidMessageException($messageName)
    {
        $message = sprintf('Unable to rehydrate from "%s" queue message, expected "update_product"', $messageName);
        return new NoUpdateProductCommandMessageException($message);
    }

    /**
     * @param mixed[] $productData
     * @return ConfigurableProduct|SimpleProduct
     */
    private static function rehydrateProduct(array $productData)
    {
        // todo: encapsulate product serialization and rehydration
        $product = $productData[Product::TYPE_KEY] === ConfigurableProduct::TYPE_CODE ?
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
