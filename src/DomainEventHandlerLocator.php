<?php

namespace Brera;

use Brera\Image\ImportImageDomainEvent;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductListingSavedDomainEvent;

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
                /* @var $event ProductImportDomainEvent */
                return $this->factory->createProductImportDomainEventHandler($event);

            case CatalogImportDomainEvent::class:
                /* @var $event CatalogImportDomainEvent */
                return $this->factory->createCatalogImportDomainEventHandler($event);

            case RootTemplateChangedDomainEvent::class:
                /* @var $event RootTemplateChangedDomainEvent */
                return $this->factory->createRootTemplateChangedDomainEventHandler($event);
            case ImportImageDomainEvent::class:
                /* @var $event ImportImageDomainEvent */
                return $this->factory->createImportImageDomainEventHandler($event);
            case ProductListingSavedDomainEvent::class:
                /* @var $event ProductListingSavedDomainEvent */
                return $this->factory->createProductListingSavedDomainEventHandler($event);
        }

        throw new UnableToFindDomainEventHandlerException(
            sprintf('Unable to find a handler for %s domain event', $eventClass)
        );
    }
}
