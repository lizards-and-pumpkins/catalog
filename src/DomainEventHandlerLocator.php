<?php

namespace Brera;

use Brera\Image\ImageImportDomainEvent;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductStockQuantityChangedDomainEvent;

class DomainEventHandlerLocator
{
    /**
     * @var DomainEventFactory
     */
    private $factory;

    public function __construct(DomainEventFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param DomainEvent $event
     * @return DomainEventHandler
     * @throws UnableToFindDomainEventHandlerException
     */
    public function getHandlerFor(DomainEvent $event)
    {
        $eventClass = get_class($event);

        switch ($eventClass) {
            case ProductImportDomainEvent::class:
                /* @var ProductImportDomainEvent $event */
                return $this->factory->createProductImportDomainEventHandler($event);

            case CatalogImportDomainEvent::class:
                /* @var CatalogImportDomainEvent $event */
                return $this->factory->createCatalogImportDomainEventHandler($event);

            case RootTemplateChangedDomainEvent::class:
                /* @var RootTemplateChangedDomainEvent $event */
                return $this->factory->createRootTemplateChangedDomainEventHandler($event);

            case ImageImportDomainEvent::class:
                /* @var ImageImportDomainEvent $event */
                return $this->factory->createImageImportDomainEventHandler($event);

            case ProductListingSavedDomainEvent::class:
                /* @var ProductListingSavedDomainEvent $event */
                return $this->factory->createProductListingSavedDomainEventHandler($event);

            case ProductStockQuantityChangedDomainEvent::class:
                /** @var ProductStockQuantityChangedDomainEvent $event */
                return $this->factory->createProductStockQuantityChangedDomainEventHandler($event);
        }

        throw new UnableToFindDomainEventHandlerException(
            sprintf('Unable to find a handler for %s domain event', $eventClass)
        );
    }
}
