<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Import\Exception\NoCatalogWasImportedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CatalogWasImportedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var CatalogWasImportedDomainEvent
     */
    private $event;
    
    public function __construct(Message $message)
    {
        $this->event = CatalogWasImportedDomainEvent::fromMessage($message);
    }

    public function process()
    {
        // Left empty till data versioning is implemented.
        // Version is already present in event and can be get by $this->event->getDataVersion()
    }
}
