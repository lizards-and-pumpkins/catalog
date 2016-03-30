<?php

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;

use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandlerFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler;

class LoggingDomainEventHandlerFactory implements Factory, DomainEventHandlerFactory
{
    use FactoryTrait;

    /**
     * @var DomainEventHandlerFactory
     */
    private $domainEventFactoryDelegate;

    public function __construct(DomainEventHandlerFactory $domainEventFactoryDelegate)
    {
        $this->domainEventFactoryDelegate = $domainEventFactoryDelegate;
    }

    /**
     * @return DomainEventHandlerFactory
     */
    private function getDomainEventFactoryDelegate()
    {
        return $this->domainEventFactoryDelegate;
    }

    /**
     * @param ProductWasUpdatedDomainEvent $event
     * @return ProductWasUpdatedDomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(ProductWasUpdatedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createProductWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param TemplateWasUpdatedDomainEvent $event
     * @return TemplateWasUpdatedDomainEventHandler
     */
    public function createTemplateWasUpdatedDomainEventHandler(TemplateWasUpdatedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createTemplateWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param ImageWasAddedDomainEvent $event
     * @return ImageWasAddedDomainEventHandler
     */
    public function createImageWasAddedDomainEventHandler(ImageWasAddedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createImageWasAddedDomainEventHandler($event)
        );
    }

    /**
     * @param ProductListingWasAddedDomainEvent $event
     * @return ProductListingWasAddedDomainEventHandler
     */
    public function createProductListingWasAddedDomainEventHandler(ProductListingWasAddedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createProductListingWasAddedDomainEventHandler($event)
        );
    }

    /**
     * @param ContentBlockWasUpdatedDomainEvent $event
     * @return ContentBlockWasUpdatedDomainEventHandler
     */
    public function createContentBlockWasUpdatedDomainEventHandler(ContentBlockWasUpdatedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createContentBlockWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param CatalogWasImportedDomainEvent $event
     * @return CatalogWasImportedDomainEventHandler
     */
    public function createCatalogWasImportedDomainEventHandler(CatalogWasImportedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createCatalogWasImportedDomainEventHandler($event)
        );
    }
}
