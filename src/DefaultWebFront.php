<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpRouterChain;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductListingImportCommandFactory;

class DefaultWebFront extends WebFront
{
    /**
     * @return MasterFactory
     */
    protected function createMasterFactory()
    {
        return new SampleMasterFactory();
    }

    protected function registerFactories(MasterFactory $masterFactory)
    {
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new TwentyOneRunFactory());
        $masterFactory->register(new UpdatingProductImportCommandFactory());
        $masterFactory->register(new UpdatingProductImageImportCommandFactory());
        $masterFactory->register(new UpdatingProductListingImportCommandFactory());
        $masterFactory->register(new FrontendFactory($this->getRequest()));
    }

    protected function registerRouters(HttpRouterChain $router)
    {
        $router->register($this->getMasterFactory()->createApiRouter());
        $router->register($this->getMasterFactory()->createProductSearchResultRouter());
        $router->register($this->getMasterFactory()->createProductSearchAutosuggestionRouter());
        $router->register($this->getMasterFactory()->createProductDetailViewRouter());
        $router->register($this->getMasterFactory()->createProductListingRouter());
        $router->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
}
