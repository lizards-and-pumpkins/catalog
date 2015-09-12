<?php


namespace Brera\Projection;

use Brera\CommonFactory;
use Brera\Content\ContentBlockWasUpdatedDomainEvent;
use Brera\Content\ContentBlockWasUpdatedDomainEventHandler;
use Brera\DomainEventFactory;
use Brera\Factory;
use Brera\FactoryTrait;
use Brera\Image\ImageWasUpdatedDomainEvent;
use Brera\Image\ImageWasUpdatedDomainEventHandler;
use Brera\Product\ProductListingWasUpdatedDomainEvent;
use Brera\Product\ProductListingWasUpdatedDomainEventHandler;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEvent;
use Brera\Product\ProductStockQuantityWasUpdatedDomainEventHandler;
use Brera\Product\ProductWasUpdatedDomainEvent;
use Brera\Product\ProductWasUpdatedDomainEventHandler;
use Brera\TemplateWasUpdatedDomainEvent;
use Brera\TemplateWasUpdatedDomainEventHandler;

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
     * @param ProductListingWasUpdatedDomainEvent $event
     * @return ProductListingWasUpdatedDomainEventHandler
     */
    public function createProductListingWasUpdatedDomainEventHandler(ProductListingWasUpdatedDomainEvent $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventDecorator(
            $domainEventFactory->createProductListingWasUpdatedDomainEventHandler($event)
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
}
