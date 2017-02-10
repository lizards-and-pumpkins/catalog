<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandlerFactory;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

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

    private function getDomainEventFactoryDelegate() : DomainEventHandlerFactory
    {
        return $this->domainEventFactoryDelegate;
    }

    public function createProductWasUpdatedDomainEventHandler() : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createProductWasUpdatedDomainEventHandler()
        );
    }

    public function createTemplateWasUpdatedDomainEventHandler() : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createTemplateWasUpdatedDomainEventHandler()
        );
    }

    public function createImageWasAddedDomainEventHandler() : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createImageWasAddedDomainEventHandler()
        );
    }

    public function createProductListingWasAddedDomainEventHandler() : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createProductListingWasAddedDomainEventHandler()
        );
    }

    public function createContentBlockWasUpdatedDomainEventHandler() : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createContentBlockWasUpdatedDomainEventHandler()
        );
    }

    public function createCatalogWasImportedDomainEventHandler() : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createCatalogWasImportedDomainEventHandler()
        );
    }

    public function createShutdownWorkerDomainEventHandler() : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createShutdownWorkerDomainEventHandler()
        );
    }

    public function createCatalogImportWasTriggeredDomainEventHandler(): DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createCatalogImportWasTriggeredDomainEventHandler()
        );
    }

    public function createCurrentDataVersionWasSetDomainEventHandler(): DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createCurrentDataVersionWasSetDomainEventHandler()
        );
    }
}
