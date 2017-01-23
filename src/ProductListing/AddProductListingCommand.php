<?php

declare(strict_types=1);

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

    public function getProductListing() : ProductListing
    {
        return $this->productListing;
    }

    public function toMessage() : Message
    {
        $name = self::CODE;
        $payload = ['listing' => $this->productListing->serialize()];
        $metadata = [];
        return Message::withCurrentTime($name, $payload, $metadata);
    }

    public static function fromMessage(Message $message): self
    {
        if ($message->getName() !== self::CODE) {
            throw self::createInvalidMessageNameException($message->getName());
        }
        $productListing = ProductListing::rehydrate($message->getPayload()['listing']);
        return new self($productListing);
    }

    private static function createInvalidMessageNameException(
        string $messageName
    ) : NoAddProductListingCommandMessageException {
        return new NoAddProductListingCommandMessageException(sprintf(
            'Unable to rehydrate from "%s" queue message, expected "%s"',
            $messageName,
            self::CODE
        ));
    }
}
