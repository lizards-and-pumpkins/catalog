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

    /**
     * @param Message $message
     * @param ProductAvailability $productAvailability
     * @return ConfigurableProduct|SimpleProduct
     */
    public static function rehydrateProduct(Message $message, ProductAvailability $productAvailability)
    {
        // TODO: encapsulate product serialization and rehydration

        self::validateMessageType($message);

        $productData = json_decode($message->getPayload()['product'], true);

        if ($productData[Product::TYPE_KEY] === ConfigurableProduct::TYPE_CODE) {
            return ConfigurableProduct::fromArray($productData, $productAvailability);
        }

        return SimpleProduct::fromArray($productData, $productAvailability);
    }

    /**
     * @param Message $message
     */
    private static function validateMessageType(Message $message)
    {
        if ($message->getName() !== 'update_product') {
            throw new NoUpdateProductCommandMessageException(sprintf(
                'Unable to rehydrate from "%s" queue message, expected "%s"',
                $message->getName(),
                UpdateProductCommand::CODE
            ));
        }
    }
}
