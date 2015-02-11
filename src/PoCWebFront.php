<?php

namespace Brera;

use Brera\Http\HttpRouterChain;

class PoCWebFront extends WebFront
{
    /**
     * @return MasterFactory
     */
    protected function createMasterFactoryIfNotInjected()
    {
        return new PoCMasterFactory();
    }

    /**
     * @param MasterFactory $factory
     */
    protected function registerFactoriesIfMasterFactoryWasNotInjected(MasterFactory $factory)
    {
        // Left empty on purpose because injected via bootstrap for PoC
        //$factory->register(new FrontendFactory());
        //$factory->register(new IntegrationTestFactory());
    }

    /**
     * @param HttpRouterChain $router
     */
    protected function registerRouters(HttpRouterChain $router)
    {
        $router->register($this->getMasterFactory()->createApiRouter());
        $router->register($this->getMasterFactory()->createUrlKeyRouter(
            $this->getRequest()->getUrl(),
            $this->getEnvironment()
        ));
    }
}
