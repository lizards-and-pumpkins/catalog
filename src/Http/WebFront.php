<?php

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Routing\HttpRouterChain;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

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
     * @var HttpRouterChain
     */
    private $routerChain;

    /**
     * @var Factory
     */
    private $implementationSpecificFactory;

    public function __construct(HttpRequest $request, Factory $implementationSpecificFactory)
    {
        $this->request = $request;
        $this->implementationSpecificFactory = $implementationSpecificFactory;
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
        $this->buildFactory();
        $this->buildRouterChain();

        $requestHandler = $this->routerChain->route($this->request);

        return $requestHandler->process($this->request);
    }

    final public function registerFactory(Factory $factory)
    {
        $this->buildFactory();
        $this->masterFactory->register($factory);
    }

    /**
     * @return MasterFactory
     */
    abstract protected function createMasterFactory();

    /**
     * @param MasterFactory $factory
     */
    abstract protected function registerFactories(MasterFactory $factory);

    /**
     * @param HttpRouterChain $router
     */
    abstract protected function registerRouters(HttpRouterChain $router);

    /**
     * @return HttpRequest
     */
    final protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Factory
     */
    final public function getImplementationSpecificFactory()
    {
        return $this->implementationSpecificFactory;
    }

    private function buildFactory()
    {
        if (null !== $this->masterFactory) {
            return;
        }
        
        $this->masterFactory = $this->createMasterFactory();
        $this->validateMasterFactory();
        $this->registerFactories($this->masterFactory);
    }

    private function buildRouterChain()
    {
        $this->routerChain = $this->masterFactory->createHttpRouterChain();
        $this->registerRouters($this->routerChain);
    }

    /**
     * @return MasterFactory
     */
    public function getMasterFactory()
    {
        $this->buildFactory();
        return $this->masterFactory;
    }

    private function validateMasterFactory()
    {
        if (!($this->masterFactory instanceof MasterFactory)) {
            throw new \InvalidArgumentException(sprintf(
                'Factory is not of type MasterFactory but "%s"',
                $this->getExceptionMessageClassNameRepresentation($this->masterFactory)
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
