<?php

declare(strict_types=1);

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

    public function getListingCriteria() : ProductListing
    {
        return $this->productListing;
    }

    public function toMessage() : Message
    {
        $payload = ['listing' => $this->productListing->serialize()];
        $version = DataVersion::fromVersionString($this->productListing->getContextData()[DataVersion::CONTEXT_CODE]);
        return Message::withCurrentTime(self::CODE, $payload, ['data_version' => (string) $version]);
    }

    public static function fromMessage(Message $message): self
    {
        if ($message->getName() !== self::CODE) {
            throw new NoProductListingWasAddedDomainEventMessage(
                sprintf('Expected "%s" domain event, got "%s"', self::CODE, $message->getName())
            );
        }
        $productListing = ProductListing::rehydrate($message->getPayload()['listing']);
        return new self($productListing);
    }

    public function getDataVersion() : DataVersion
    {
        return DataVersion::fromVersionString($this->getListingCriteria()->getContextData()[DataVersion::CONTEXT_CODE]);
    }
}
