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
    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(Message $event);

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createTemplateWasUpdatedDomainEventHandler(Message $event);

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createImageWasAddedDomainEventHandler(Message $event);

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createProductListingWasAddedDomainEventHandler(Message $event);

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createContentBlockWasUpdatedDomainEventHandler(Message $event);

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createCatalogWasImportedDomainEventHandler(Message $event);
}
