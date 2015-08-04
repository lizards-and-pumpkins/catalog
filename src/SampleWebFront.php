<?php

namespace Brera;

use Brera\Context\Context;
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

    /**
     * @var FrontendFactory
     */
    private $frontendFactory;

    protected function registerFactories(MasterFactory $masterFactory)
    {
        $this->registerSharedFactories($masterFactory);
        $this->registerFrontendFactory($masterFactory);
    }

    protected function registerSharedFactories(MasterFactory $masterFactory)
    {
        $masterFactory->register(new CommonFactory());
    }

    protected function registerFrontendFactory(MasterFactory $masterFactory)
    {
        $frontendFactory = new FrontendFactory();
        $this->setFrontendFactoryForTestability($frontendFactory);
        $masterFactory->register($frontendFactory);
    }

    protected function setFrontendFactoryForTestability(FrontendFactory $frontendFactory)
    {
        $this->frontendFactory = $frontendFactory;
    }

    /**
     * @param HttpRequest $request
     * @return Context
     */
    protected function createContext(HttpRequest $request)
    {
        /** @var ContextBuilder $contextBuilder */
        $contextBuilder = $this->getMasterFactory()->createContextBuilder();
        $context = $contextBuilder->createFromRequest($request);
        $this->frontendFactory->setContext($context);
        return $context;
    }

    protected function registerRouters(HttpRouterChain $router)
    {
        $router->register($this->getMasterFactory()->createApiRouter());
        $router->register($this->getMasterFactory()->createProductDetailViewRouter());
        $router->register($this->getMasterFactory()->createProductListingRouter());
        $router->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
}
