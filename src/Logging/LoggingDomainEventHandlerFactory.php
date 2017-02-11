<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandlerFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class LoggingDomainEventHandlerFactory implements Factory, DomainEventHandlerFactory
{
    use FactoryTrait;

    /**
     * @var MasterFactory
     */
    private $masterFactory;

    /**
     * @var DomainEventHandler[]
     */
    private $nonDecoratedEventHandlers;

    public function __construct(MasterFactory $masterFactory)
    {
        $this->nonDecoratedEventHandlers = [
            'ProductWasUpdatedDomainEventHandler'         =>
                $masterFactory->createProductWasUpdatedDomainEventHandler(),
            'TemplateWasUpdatedDomainEventHandler'        =>
                $masterFactory->createTemplateWasUpdatedDomainEventHandler(),
            'ImageWasAddedDomainEventHandler'             =>
                $masterFactory->createImageWasAddedDomainEventHandler(),
            'ProductListingWasAddedDomainEventHandler'    =>
                $masterFactory->createProductListingWasAddedDomainEventHandler(),
            'ContentBlockWasUpdatedDomainEventHandler'    =>
                $masterFactory->createContentBlockWasUpdatedDomainEventHandler(),
            'CatalogWasImportedDomainEventHandler'        =>
                $masterFactory->createCatalogWasImportedDomainEventHandler(),
            'ShutdownWorkerDomainEventHandler'            =>
                $masterFactory->createShutdownWorkerDomainEventHandler(),
            'CatalogImportWasTriggeredDomainEventHandler' =>
                $masterFactory->createCatalogImportWasTriggeredDomainEventHandler(),
            'CurrentDataVersionWasSetDomainEventHandler'  =>
                $masterFactory->createCurrentDataVersionWasSetDomainEventHandler(),
        ];
        $this->masterFactory = $masterFactory;
    }

    private function getDelegate(string $method): DomainEventHandler
    {
        $key = $this->getClassToInstantiateFromCreateMethod($method);

        return $this->nonDecoratedEventHandlers[$key];
    }

    private function getClassToInstantiateFromCreateMethod(string $method): string
    {
        return substr($method, 6);
    }

    public function createProcessTimeLoggingDomainEventHandlerDecorator(
        DomainEventHandler $eventHandlerToDecorate
    ): ProcessTimeLoggingDomainEventHandlerDecorator {
        return new ProcessTimeLoggingDomainEventHandlerDecorator(
            $eventHandlerToDecorate,
            $this->masterFactory->getLogger()
        );
    }

    public function createProductWasUpdatedDomainEventHandler(): DomainEventHandler
    {
        return $this->masterFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createTemplateWasUpdatedDomainEventHandler(): DomainEventHandler
    {
        return $this->masterFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createImageWasAddedDomainEventHandler(): DomainEventHandler
    {
        return $this->masterFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createProductListingWasAddedDomainEventHandler(): DomainEventHandler
    {
        return $this->masterFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createContentBlockWasUpdatedDomainEventHandler(): DomainEventHandler
    {
        return $this->masterFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createCatalogWasImportedDomainEventHandler(): DomainEventHandler
    {
        return $this->masterFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createShutdownWorkerDomainEventHandler(): DomainEventHandler
    {
        return $this->masterFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createCatalogImportWasTriggeredDomainEventHandler(): DomainEventHandler
    {
        return $this->masterFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }

    public function createCurrentDataVersionWasSetDomainEventHandler(): DomainEventHandler
    {
        return $this->masterFactory->createProcessTimeLoggingDomainEventHandlerDecorator(
            $this->getDelegate(__FUNCTION__)
        );
    }
}
