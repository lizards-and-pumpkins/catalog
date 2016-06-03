<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Exception\NoProductListingWasAddedDomainEventMessage;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

class ProductListingWasAddedDomainEvent implements DomainEvent
{
    const CODE = 'product_listing_was_added';
    
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
    public function getListingCriteria()
    {
        return $this->productListing;
    }

    /**
     * @return Message
     */
    public function toMessage()
    {
        $payload = ['listing' => $this->productListing->serialize()];
        $version = DataVersion::fromVersionString($this->productListing->getContextData()[DataVersion::CONTEXT_CODE]);
        return Message::withCurrentTime(self::CODE, $payload, ['data_version' => (string) $version]);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        if ($message->getName() !== self::CODE) {
            throw new NoProductListingWasAddedDomainEventMessage(
                sprintf('Expected "%s" domain event, got "%s"', self::CODE, $message->getName())
            );
        }
        $productListing = ProductListing::rehydrate($message->getPayload()['listing']);
        return new self($productListing);
    }

    /**
     * @return DataVersion
     */
    public function getDataVersion()
    {
        return DataVersion::fromVersionString($this->getListingCriteria()->getContextData()[DataVersion::CONTEXT_CODE]);
    }
}
