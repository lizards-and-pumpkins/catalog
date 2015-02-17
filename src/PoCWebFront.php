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
     * @return HttpRequest
     */
    protected function createContext(HttpRequest $request)
    {
        /** @var ContextBuilder $contextBuilder */
        $contextBuilder = $this->getMasterFactory()->createContextBuilder();

        return $contextBuilder->getContext(
            ['website' => 'ru_de', 'language' => 'de_DE']
        );
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
        $router->register($this->getMasterFactory()->createUrlKeyRouter(
            $this->getRequest()->getUrl(),
            $this->getContext()
        ));
        $router->register($this->getMasterFactory()->createResourceNotFoundRouter());
    }
}
