<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CatalogImportWasTriggeredDomainEventHandler implements DomainEventHandler
{
    /**
     * @var CatalogImport
     */
    private $catalogImport;

    /**
     * @var CatalogImportWasTriggeredDomainEvent
     */
    private $catalogImportWasTriggeredEvent;

    public function __construct(
        CatalogImport $catalogImport,
        Message $eventMessage
    ) {
        $this->catalogImport = $catalogImport;
        $this->catalogImportWasTriggeredEvent = CatalogImportWasTriggeredDomainEvent::fromMessage($eventMessage);
    }

    public function process()
    {
        $this->catalogImport->importFile($this->catalogImportWasTriggeredEvent->getCatalogImportFilePath());
    }
}
