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
    
    public function __construct(CatalogImport $catalogImport) {
        $this->catalogImport = $catalogImport;
    }

    public function process(Message $message): void
    {
        $catalogImportWasTriggeredEvent = CatalogImportWasTriggeredDomainEvent::fromMessage($message);
        $this->catalogImport->importFile(
            $catalogImportWasTriggeredEvent->getCatalogImportFilePath(),
            $catalogImportWasTriggeredEvent->getDataVersion()
        );
    }
}
