<?php

namespace Brera\Product;

use Brera\DomainEventHandler;
use Brera\Image\ImageImportDomainEvent;
use Brera\Queue\Queue;
use Brera\Utils\XPathParser;

class CatalogImportDomainEventHandler implements DomainEventHandler
{
    /**
     * @var CatalogImportDomainEvent
     */
    private $event;

    /**
     * @var Queue
     */
    private $eventQueue;

    public function __construct(CatalogImportDomainEvent $event, Queue $eventQueue)
    {
        $this->event = $event;
        $this->eventQueue = $eventQueue;
    }

    public function process()
    {
        $xml = $this->event->getXml();

        $this->emitProductWasUpdatedDomainEvents($xml);
        $this->emitProductListingSavedDomainEvents($xml);
        $this->emitImageImportDomainEvents($xml);
    }

    /**
     * @param string $xml
     */
    private function emitProductWasUpdatedDomainEvents($xml)
    {
        $productNodesXml = (new XPathParser($xml))->getXmlNodesRawXmlArrayByXPath('//catalog/products/product');
        foreach ($productNodesXml as $productXml) {
            $this->eventQueue->add(new ProductWasUpdatedDomainEvent($productXml));
        }
    }

    /**
     * @param string $xml
     */
    private function emitProductListingSavedDomainEvents($xml)
    {
        $listingNodesXml = (new XPathParser($xml))->getXmlNodesRawXmlArrayByXPath('//catalog/listings/listing');
        foreach ($listingNodesXml as $listingXml) {
            $this->eventQueue->add(new ProductListingSavedDomainEvent($listingXml));
        }
    }

    /**
     * @param string $xml
     */
    private function emitImageImportDomainEvents($xml)
    {
        $imageNodes = (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/image/file'
        );
        foreach ($imageNodes as $imageNode) {
            $this->eventQueue->add(new ImageImportDomainEvent($imageNode['value']));
        }
    }
}
