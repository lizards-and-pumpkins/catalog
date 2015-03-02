<?php

namespace Brera;

use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;

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
     * @param RootSnippetChangedDomainEvent $event
     * @return RootSnippetChangedDomainEventHandler
     */
    public function createRootSnippetChangedDomainEventHandler(RootSnippetChangedDomainEvent $event);
}
