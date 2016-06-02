<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Exception\NoAddProductListingCommandMessageException;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

class AddProductListingCommand implements Command
{
    const CODE = 'add_product_listing';

    /**
     * @var ProductListing
     */
    private $productListing;

    public function __construct(ProductListing $productListing)
    {
        $this->productListing = $productListing;
    }

    /**
     * @return ProductListing
     */
    public function getProductListing()
    {
        return $this->productListing;
    }

    /**
     * @return Message
     */
    public function toMessage()
    {
        $name = self::CODE;
        $payload = json_encode(['listing' => $this->productListing->serialize()]);
        $metadata = [];
        return Message::withCurrentTime($name, $payload, $metadata);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        if ($message->getName() !== self::CODE) {
            throw self::createInvalidMessageNameException($message->getName());
        }
        $payload = json_decode($message->getPayload(), true);
        $productListing = ProductListing::rehydrate($payload['listing']);
        return new self($productListing);
    }

    /**
     * @param string $messageName
     * @return NoAddProductListingCommandMessageException
     */
    private static function createInvalidMessageNameException($messageName)
    {
        return new NoAddProductListingCommandMessageException(sprintf(
            'Unable to rehydrate from "%s" queue message, expected "add_product_listing"',
            $messageName
        ));
    }
}
