<?php


namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\CommonFactory;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Content\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\DomainEventFactory;
use LizardsAndPumpkins\Factory;
use LizardsAndPumpkins\FactoryTrait;
use LizardsAndPumpkins\Image\ImageWasUpdatedDomainEvent;
use LizardsAndPumpkins\Image\ImageWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEvent;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEventHandler;
use LizardsAndPumpkins\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\TemplateWasUpdatedDomainEventHandler;

class LoggingDomainEventHandlerFactory implements Factory, DomainEventFactory
{
    use FactoryTrait;

    /**
     * @var DomainEventFactory
     */
    private $domainEventFactoryDelegate;

    /**
     * @return DomainEventFactory
     */
    private function getDomainEventFactoryDelegate()
    {
        if (null === $this->domainEventFactoryDelegate) {
            $this->domainEventFactoryDelegate = new CommonFactory();
            $this->domainEventFactoryDelegate->setMasterFactory($this->getMasterFactory());
        }
        return $this->domainEventFactoryDelegate;
    }

    /**
     * @param ProductWasUpdatedDomainEvent $event
     * @return ProductWasUpdatedDomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(ProductWasUpdatedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventDecorator(
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
        return $domainEventFactory->createProcessTimeLoggingDomainEventDecorator(
            $domainEventFactory->createTemplateWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param ImageWasUpdatedDomainEvent $event
     * @return ImageWasUpdatedDomainEventHandler
     */
    public function createImageWasUpdatedDomainEventHandler(ImageWasUpdatedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventDecorator(
            $domainEventFactory->createImageWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param ProductListingWasAddedDomainEvent $event
     * @return ProductListingWasAddedDomainEventHandler
     */
    public function createProductListingWasAddedDomainEventHandler(ProductListingWasAddedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventDecorator(
            $domainEventFactory->createProductListingWasAddedDomainEventHandler($event)
        );
    }

    /**
     * @param ProductStockQuantityWasUpdatedDomainEvent $event
     * @return ProductStockQuantityWasUpdatedDomainEventHandler
     */
    public function createProductStockQuantityWasUpdatedDomainEventHandler(
        ProductStockQuantityWasUpdatedDomainEvent $event
    ) {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventDecorator(
            $domainEventFactory->createProductStockQuantityWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param ContentBlockWasUpdatedDomainEvent $event
     * @return ContentBlockWasUpdatedDomainEventHandler
     */
    public function createContentBlockWasUpdatedDomainEventHandler(ContentBlockWasUpdatedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventDecorator(
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
        return $domainEventFactory->createProcessTimeLoggingDomainEventDecorator(
            $domainEventFactory->createCatalogWasImportedDomainEventHandler($event)
        );
    }
}
