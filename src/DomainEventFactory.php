<?php

namespace Brera;

use Brera\Content\ContentBlockWasUpdatedDomainEvent;
use Brera\Content\ContentBlockWasUpdatedDomainEventHandler;
use Brera\Image\ImageImportDomainEvent;
use Brera\Image\ImageImportDomainEventHandler;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductListingSavedDomainEventHandler;
use Brera\Product\ProductStockQuantityChangedDomainEvent;
use Brera\Product\ProductStockQuantityChangedDomainEventHandler;

interface DomainEventFactory
{
    /**
     * @param ProductImportDomainEvent $event
     * @return ProductImportDomainEventHandler
     */
    public function createProductImportDomainEventHandler(ProductImportDomainEvent $event);

    /**
     * @param CatalogImportDomainEvent $event
     * @return CatalogImportDomainEventHandler
     */
    public function createCatalogImportDomainEventHandler(CatalogImportDomainEvent $event);

    /**
     * @param RootTemplateChangedDomainEvent $event
     * @return RootTemplateChangedDomainEventHandler
     */
    public function createRootTemplateChangedDomainEventHandler(RootTemplateChangedDomainEvent $event);

    /**
     * @param ImageImportDomainEvent $event
     * @return ImageImportDomainEventHandler
     */
    public function createImageImportDomainEventHandler(ImageImportDomainEvent $event);

    /**
     * @param ProductListingSavedDomainEvent $event
     * @return ProductListingSavedDomainEventHandler
     */
    public function createProductListingSavedDomainEventHandler(ProductListingSavedDomainEvent $event);

    /**
     * @param ProductStockQuantityChangedDomainEvent $event
     * @return ProductStockQuantityChangedDomainEventHandler
     */
    public function createProductStockQuantityChangedDomainEventHandler(ProductStockQuantityChangedDomainEvent $event);

    /**
     * @param ContentBlockWasUpdatedDomainEvent $event
     * @return ContentBlockWasUpdatedDomainEventHandler
     */
    public function createContentBlockWasUpdatedDomainEventHandler(ContentBlockWasUpdatedDomainEvent $event);
}
