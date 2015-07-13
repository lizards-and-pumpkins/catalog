<?php

namespace Brera;

use Brera\Image\ImageImportDomainEvent;
use Brera\Image\ImageImportDomainEventHandler;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductListingSavedDomainEventHandler;
use Brera\Product\ProductStockQuantityUpdatedDomainEvent;
use Brera\Product\ProductStockQuantityUpdatedDomainEventHandler;

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
     * @param ProductStockQuantityUpdatedDomainEvent $event
     * @return ProductStockQuantityUpdatedDomainEventHandler
     */
    public function createProductStockQuantityUpdatedDomainEventHandler(ProductStockQuantityUpdatedDomainEvent $event);
}
