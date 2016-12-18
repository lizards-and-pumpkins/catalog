<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface DomainEventHandlerFactory
{
    public function createProductWasUpdatedDomainEventHandler(Message $event) : DomainEventHandler;

    public function createTemplateWasUpdatedDomainEventHandler(Message $event) : DomainEventHandler;

    public function createImageWasAddedDomainEventHandler(Message $event) : DomainEventHandler;

    public function createProductListingWasAddedDomainEventHandler(Message $event) : DomainEventHandler;

    public function createContentBlockWasUpdatedDomainEventHandler(Message $event) : DomainEventHandler;

    public function createCatalogWasImportedDomainEventHandler(Message $event) : DomainEventHandler;
}
