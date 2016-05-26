<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Import\Exception\NoCatalogWasImportedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CatalogWasImportedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var Message
     */
    private $event;
    
    public function __construct(Message $event)
    {
        if ($event->getName() !== 'catalog_was_imported_domain_event') {
            $message = sprintf('Expected "catalog_was_imported" domain event, got "%s"', $event->getName());
            throw new NoCatalogWasImportedDomainEventMessageException($message);
        }
        $this->event = $event;
    }

    public function process()
    {
        // Left empty till data versioning is implemented.
        // Version is already present in event and can be get by $this->event->getDataVersion()
    }
}
