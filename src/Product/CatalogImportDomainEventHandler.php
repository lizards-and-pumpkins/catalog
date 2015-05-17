<?php

namespace Brera\Product;

use Brera\DomainEventHandler;
use Brera\Image\ImageImportDomainEvent;
use Brera\Queue\Queue;
use Brera\XPathParser;

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

        $this->emitProductImportDomainEvents($xml);
        $this->emitImageImportDomainEvents($xml);
    }

    /**
     * @param string $xml
     */
    private function emitProductImportDomainEvents($xml)
    {
        $productNodesXml = (new XPathParser($xml))->getXmlNodesRawXmlArrayByXPath('product');
        foreach ($productNodesXml as $productXml) {
            $this->eventQueue->add(new ProductImportDomainEvent($productXml));
        }
    }

    /**
     * @param string $xml
     */
    private function emitImageImportDomainEvents($xml)
    {
        $imageNodes = (new XPathParser($xml))->getXmlNodesArrayByXPath('product/attributes/image/file');
        foreach ($imageNodes as $imageNode) {
            $this->eventQueue->add(new ImageImportDomainEvent($imageNode['value']));
        }
    }
}
