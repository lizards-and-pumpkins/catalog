<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CatalogWasImportedDomainEventHandler implements DomainEventHandler
{
    public function process(Message $message): void
    {
        // Left empty till data versioning and event sourcing is implemented.
        // Version is already present in event and can be get by $event->getDataVersion()
        $event = CatalogWasImportedDomainEvent::fromMessage($message);
    }
}
