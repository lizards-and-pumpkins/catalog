<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Core\Factory\MasterFactory;
use LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory;
use LizardsAndPumpkins\Http\HttpFactory;
use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Http\WebFront;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchSharedFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;

class CatalogWebFront extends WebFront
{
    protected function createMasterFactory(): MasterFactory
    {
        return new CatalogMasterFactory();
    }

    protected function registerFactories(MasterFactory $masterFactory): void
    {
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new HttpFactory());
        $masterFactory->register(new ProductSearchSharedFactory());
        $masterFactory->register(new FrontendFactory($this->getRequest()));
        $masterFactory->register($this->getImplementationSpecificFactory());
    }

    protected function registerRouters(HttpRouterChain $routerChain): void
    {
        $routerChain->register($this->getMasterFactory()->createUnknownHttpRequestMethodRouter());
        $routerChain->register($this->getMasterFactory()->createMetaSnippetBasedRouter());
        $routerChain->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
}
