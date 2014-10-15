<?php


namespace Brera\PoC;


class PoCShop extends Shop
{
    /**
     * @return MasterFactory
     */
    protected function createMasterFactory()
    {
        return new PoCMasterFactory();
    }

    /**
     * @param MasterFactory $factory
     */
    protected function registerFactories(MasterFactory $factory)
    {
        $factory->register(new FrontendFactory());
        // live implementation
        // $factory->register(new IntegrationTestFactory());
    }

    /**
     * @param HttpRouterChain $router
     */
    protected function registerRouters(HttpRouterChain $router)
    {
        $router->register($this->getMasterFactory()->createProductSeoUrlRouter());
    }

} 
