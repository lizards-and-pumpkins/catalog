<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;

class CatalogWasImportedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var CatalogWasImportedDomainEvent
     */
    private $event;
    
    public function __construct(CatalogWasImportedDomainEvent $event)
    {
        $this->event = $event;
    }

    public function process()
    {
        // Left empty till data versioning is implemented.
        // Version is already present in event and can be get by $this->event->getDataVersion()
    }
}
