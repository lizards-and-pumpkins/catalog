<?php

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandlerFactory;
use LizardsAndPumpkins\Messaging\Queue\Message;
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

    public function createProductWasUpdatedDomainEventHandler(Message $event): DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createProductWasUpdatedDomainEventHandler($event)
        );
    }

    public function createTemplateWasUpdatedDomainEventHandler(Message $event): DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createTemplateWasUpdatedDomainEventHandler($event)
        );
    }

    public function createImageWasAddedDomainEventHandler(Message $event): DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createImageWasAddedDomainEventHandler($event)
        );
    }

    public function createProductListingWasAddedDomainEventHandler(Message $event): DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createProductListingWasAddedDomainEventHandler($event)
        );
    }

    public function createContentBlockWasUpdatedDomainEventHandler(Message $event): DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createContentBlockWasUpdatedDomainEventHandler($event)
        );
    }

    public function createCatalogWasImportedDomainEventHandler(Message $event): DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createCatalogWasImportedDomainEventHandler($event)
        );
    }
}
