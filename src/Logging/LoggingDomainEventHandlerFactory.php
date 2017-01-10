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

    public function createProductWasUpdatedDomainEventHandler(Message $event) : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createProductWasUpdatedDomainEventHandler($event)
        );
    }

    public function createTemplateWasUpdatedDomainEventHandler(Message $event) : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createTemplateWasUpdatedDomainEventHandler($event)
        );
    }

    public function createImageWasAddedDomainEventHandler(Message $event) : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createImageWasAddedDomainEventHandler($event)
        );
    }

    public function createProductListingWasAddedDomainEventHandler(Message $event) : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createProductListingWasAddedDomainEventHandler($event)
        );
    }

    public function createContentBlockWasUpdatedDomainEventHandler(Message $event) : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createContentBlockWasUpdatedDomainEventHandler($event)
        );
    }

    public function createCatalogWasImportedDomainEventHandler(Message $event) : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createCatalogWasImportedDomainEventHandler($event)
        );
    }

    public function createShutdownWorkerDomainEventHandler(Message $event) : DomainEventHandler
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createShutdownWorkerDomainEventHandler($event)
        );
    }
}
