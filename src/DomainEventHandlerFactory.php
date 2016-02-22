<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEventHandler;

interface DomainEventHandlerFactory
{
    /**
     * @param ProductWasUpdatedDomainEvent $event
     * @return ProductWasUpdatedDomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(ProductWasUpdatedDomainEvent $event);

    /**
     * @param TemplateWasUpdatedDomainEvent $event
     * @return TemplateWasUpdatedDomainEventHandler
     */
    public function createTemplateWasUpdatedDomainEventHandler(TemplateWasUpdatedDomainEvent $event);

    /**
     * @param ImageWasAddedDomainEvent $event
     * @return ImageWasAddedDomainEventHandler
     */
    public function createImageWasAddedDomainEventHandler(ImageWasAddedDomainEvent $event);

    /**
     * @param ProductListingWasAddedDomainEvent $event
     * @return ProductListingWasAddedDomainEventHandler
     */
    public function createProductListingWasAddedDomainEventHandler(ProductListingWasAddedDomainEvent $event);

    /**
     * @param ContentBlockWasUpdatedDomainEvent $event
     * @return ContentBlockWasUpdatedDomainEventHandler
     */
    public function createContentBlockWasUpdatedDomainEventHandler(ContentBlockWasUpdatedDomainEvent $event);

    /**
     * @param CatalogWasImportedDomainEvent $event
     * @return CatalogWasImportedDomainEventHandler
     */
    public function createCatalogWasImportedDomainEventHandler(CatalogWasImportedDomainEvent $event);
}
