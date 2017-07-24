<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Http\WebFront;
use LizardsAndPumpkins\Logging\LoggingQueueFactory;
use LizardsAndPumpkins\Logging\LoggingCommandHandlerFactory;
use LizardsAndPumpkins\Logging\LoggingDomainEventHandlerFactory;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchSharedFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class DefaultWebFront extends WebFront
{
    protected function createMasterFactory() : MasterFactory
    {
        return new CatalogMasterFactory();
    }

    protected function registerFactories(MasterFactory $masterFactory)
    {
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new ProductSearchSharedFactory());
        $masterFactory->register(new FrontendFactory($this->getRequest()));
        $masterFactory->register($this->getImplementationSpecificFactory());
        //$this->enableDebugLogging($masterFactory, $commonFactory, $implementationFactory);
    }

    private function enableDebugLogging(MasterFactory $masterFactory, CommonFactory $commonFactory)
    {
        $masterFactory->register(new LoggingQueueFactory($this->getImplementationSpecificFactory()));
        $masterFactory->register(new LoggingCommandHandlerFactory($commonFactory));
        $masterFactory->register(new LoggingDomainEventHandlerFactory($commonFactory));
    }

    protected function registerRouters(HttpRouterChain $routerChain)
    {
        $routerChain->register($this->getMasterFactory()->createUnknownHttpRequestMethodRouter());
        $routerChain->register($this->getMasterFactory()->createMetaSnippetBasedRouter());
        $routerChain->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
}
