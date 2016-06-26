<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Exception\NoUpdateProductCommandMessageException;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateProductCommandBuilder
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
        if ($message->getName() !== 'update_product') {
            throw new NoUpdateProductCommandMessageException(sprintf(
                'Unable to rehydrate from "%s" queue message, expected "%s"',
                $message->getName(),
                UpdateProductCommand::CODE
            ));
        }

        $product = self::rehydrateProduct(json_decode($message->getPayload()['product'], true));

        return new UpdateProductCommand($product);
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
