<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;

class CatalogImportWasTriggeredDomainEventHandler implements DomainEventHandler
{
    /**
     * @var CatalogImport
     */
    private $catalogImport;

    /**
     * @var CatalogImportWasTriggeredEvent
     */
    private $catalogImportWasTriggeredEvent;

    public function __construct(
        CatalogImport $catalogImport,
        CatalogImportWasTriggeredEvent $catalogImportWasTriggeredEvent
    ) {
        $this->catalogImport = $catalogImport;
        $this->catalogImportWasTriggeredEvent = $catalogImportWasTriggeredEvent;
    }

    public function process()
    {
        $this->catalogImport->importFile($this->catalogImportWasTriggeredEvent->getCatalogImportFilePath());
    }
}
