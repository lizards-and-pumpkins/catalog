<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Event;

interface DomainEventHandlerFactory
{
    public function createProductWasUpdatedDomainEventHandler(): DomainEventHandler;

    public function createTemplateWasUpdatedDomainEventHandler(): DomainEventHandler;

    public function createImageWasAddedDomainEventHandler(): DomainEventHandler;

    public function createProductListingWasAddedDomainEventHandler(): DomainEventHandler;

    public function createContentBlockWasUpdatedDomainEventHandler(): DomainEventHandler;

    public function createCatalogWasImportedDomainEventHandler(): DomainEventHandler;

    public function createShutdownWorkerDomainEventHandler(): DomainEventHandler;

    public function createCatalogImportWasTriggeredDomainEventHandler(): DomainEventHandler;
    
    public function createCurrentDataVersionWasSetDomainEventHandler(): DomainEventHandler;
}
