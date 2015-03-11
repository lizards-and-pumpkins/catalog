<?php

namespace Brera\Product;

use Brera\XPathParser;
use Brera\Queue\Queue;
use Brera\DomainEventHandler;

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

    /**
     * @param CatalogImportDomainEvent $event
     * @param Queue $eventQueue
     */
    public function __construct(CatalogImportDomainEvent $event, Queue $eventQueue)
    {
        $this->event = $event;
        $this->eventQueue = $eventQueue;
    }

    public function process()
    {
        $xml = $this->event->getXml();

        $productNodesXml = (new XPathParser($xml))->getXmlNodesRawXmlArrayByXPath('product');
        foreach ($productNodesXml as $productXml) {
            $this->eventQueue->add(new ProductImportDomainEvent($productXml));
        }
    }
}
