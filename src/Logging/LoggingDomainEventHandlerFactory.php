<?php

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

    /**
     * @return DomainEventHandlerFactory
     */
    private function getDomainEventFactoryDelegate()
    {
        return $this->domainEventFactoryDelegate;
    }

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createProductWasUpdatedDomainEventHandler(Message $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createProductWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createTemplateWasUpdatedDomainEventHandler(Message $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createTemplateWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createImageWasAddedDomainEventHandler(Message $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createImageWasAddedDomainEventHandler($event)
        );
    }

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createProductListingWasAddedDomainEventHandler(Message $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createProductListingWasAddedDomainEventHandler($event)
        );
    }

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createContentBlockWasUpdatedDomainEventHandler(Message $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createContentBlockWasUpdatedDomainEventHandler($event)
        );
    }

    /**
     * @param Message $event
     * @return DomainEventHandler
     */
    public function createCatalogWasImportedDomainEventHandler(Message $event)
    {
        $domainEventFactory = $this->getDomainEventFactoryDelegate();
        return $domainEventFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $domainEventFactory->createCatalogWasImportedDomainEventHandler($event)
        );
    }
}
