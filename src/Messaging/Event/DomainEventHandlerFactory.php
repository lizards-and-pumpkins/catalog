<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Queue\Message;

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
