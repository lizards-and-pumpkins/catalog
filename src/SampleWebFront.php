<?php

namespace Brera;

use Brera\Context\ContextBuilder;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRouterChain;

class SampleWebFront extends WebFront
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
        $masterFactory->register(new FrontendFactory());
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

    protected function registerRouters(HttpRouterChain $router)
    {
        $router->register($this->getMasterFactory()->createApiRouter());
        $router->register($this->getMasterFactory()->createProductDetailViewRouter());
        $router->register($this->getMasterFactory()->createProductListingRouter());
        $router->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
}
