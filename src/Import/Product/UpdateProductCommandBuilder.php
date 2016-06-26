<?php

namespace LizardsAndPumpkins\Import\Product;

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

    /**
     * @param Message $message
     * @return UpdateProductCommand
     */
    public function fromMessage(Message $message)
    {
        $product = UpdateProductCommand::rehydrateProduct($message, $this->productAvailability);
        return new UpdateProductCommand($product);
    }
}
