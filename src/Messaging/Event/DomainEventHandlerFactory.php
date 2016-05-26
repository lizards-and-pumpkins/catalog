<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;

interface DomainEventHandlerFactory
{
    public function createProductWasUpdatedDomainEventHandler(Message $event): DomainEventHandler;

    public function createTemplateWasUpdatedDomainEventHandler(Message $event): DomainEventHandler;

    public function createImageWasAddedDomainEventHandler(Message $event): DomainEventHandler;

    public function createProductListingWasAddedDomainEventHandler(Message $event): DomainEventHandler;

    public function createContentBlockWasUpdatedDomainEventHandler(Message $event): DomainEventHandler;

    public function createCatalogWasImportedDomainEventHandler(Message $event): DomainEventHandler;
}
