<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpRequest;
use Brera\Http\HttpResponse;
use Brera\Http\HttpRouterChain;

abstract class WebFront
{
    /**
     * @var MasterFactory
     */
    private $masterFactory;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param HttpRequest $request
     * @param MasterFactory $factory
     */
    public function __construct(HttpRequest $request, MasterFactory $factory = null)
    {
        $this->request = $request;
        $this->masterFactory = $factory;
    }

    /**
     * @return HttpResponse
     */
    public function run()
    {
        $response = $this->runWithoutSendingResponse();
        $response->send();
        return $response;
    }

    /**
     * @return HttpResponse
     */
    public function runWithoutSendingResponse()
    {
        $this->buildFactoryIfItWasNotInjected();
        $this->buildContext();
        
        /** @var HttpRouterChain $routerChain */
        $routerChain = $this->getMasterFactory()->createHttpRouterChain();
        $this->registerRouters($routerChain);

        $requestHandler = $routerChain->route($this->request, $this->context);

        // TODO put response creation into factory, response depends on http version!

        return $requestHandler->process();
    }

    final public function registerFactory(Factory $factory)
    {
        $this->buildFactoryIfItWasNotInjected();
        $this->masterFactory->register($factory);
    }

    /**
     * @return MasterFactory
     */
    abstract protected function createMasterFactoryIfNotInjected();

    /**
     * @param HttpRequest $request
     */
    abstract protected function createContext(HttpRequest $request);

    /**
     * @param MasterFactory $factory
     */
    abstract protected function registerFactoriesIfMasterFactoryWasNotInjected(MasterFactory $factory);

    /**
     * @param HttpRouterChain $router
     */
    abstract protected function registerRouters(HttpRouterChain $router);

    /**
     * @return void
     */
    private function buildFactoryIfItWasNotInjected()
    {
        if (null !== $this->masterFactory) {
            return;
        }

        $this->masterFactory = $this->createMasterFactoryIfNotInjected();
        $this->validateMasterFactory();
        $this->registerFactoriesIfMasterFactoryWasNotInjected($this->masterFactory);
    }

    /**
     * @return MasterFactory
     */
    public function getMasterFactory()
    {
        return $this->masterFactory;
    }

    /**
     * @return Context
     */
    final protected function getContext()
    {
        return $this->context;
    }

    /**
     * @return HttpRequest
     */
    final protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validateMasterFactory()
    {
        if (!($this->masterFactory instanceof MasterFactory)) {
            throw new \InvalidArgumentException(sprintf(
                'Factory is not of type MasterFactory but "%s"',
                $this->getExceptionMessageClassNameRepresentation($this->masterFactory)
            ));
        }
    }

    private function buildContext()
    {
        $this->context = $this->createContext($this->request);
        $this->validateContext();
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validateContext()
    {
        if (!($this->context instanceof Context)) {
            throw new \InvalidArgumentException(sprintf(
                'Context is not of type Context but "%s"',
                $this->getExceptionMessageClassNameRepresentation($this->context)
            ));
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function getExceptionMessageClassNameRepresentation($value)
    {
        if (is_object($value)) {
            return get_class($value);
        }

        if (is_null($value)) {
            return 'NULL';
        }

        return (string) $value;
    }
}
