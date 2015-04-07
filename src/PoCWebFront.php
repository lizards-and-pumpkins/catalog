<?php

namespace Brera;

use Brera\Context\ContextBuilder;
use Brera\Http\HttpRequest;
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
     * @param HttpRequest $request
     * @return Context\Context
     */
    protected function createContext(HttpRequest $request)
    {
        /** @var ContextBuilder $contextBuilder */
        $contextBuilder = $this->getMasterFactory()->createContextBuilder();
        return $contextBuilder->createFromRequest($request);
    }

    /**
     * @param MasterFactory $factory
     */
    protected function registerFactoriesIfMasterFactoryWasNotInjected(MasterFactory $factory)
    {
        $factory->register(new CommonFactory());
        $factory->register(new FrontendFactory());
    }

    /**
     * @param HttpRouterChain $router
     */
    protected function registerRouters(HttpRouterChain $router)
    {
        $router->register($this->getMasterFactory()->createApiRouter());
        $router->register($this->getMasterFactory()->createProductDetailViewRouter());
        $router->register($this->getMasterFactory()->createProductListingRouter());
        $router->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
}
