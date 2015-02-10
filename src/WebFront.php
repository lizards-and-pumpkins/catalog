<?php

namespace Brera;

use Brera\Environment\Environment;
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
     * @var Environment
     */
    private $environment;

    /**
     * @param HttpRequest $request
     * @param Environment $environment
     * @param MasterFactory $factory
     */
    public function __construct(HttpRequest $request, Environment $environment, MasterFactory $factory = null)
    {
        $this->request = $request;
        $this->environment = $environment;
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

        $router = new HttpRouterChain();
        $this->registerRouters($router);

        $requestHandler = $router->route($this->request, $this->environment);

        // TODO put response creation into factory, response depends on http version!
        
        return $requestHandler->process();

    }

    /**
     * @return MasterFactory
     */
    abstract protected function createMasterFactoryIfNotInjected();

    /**
     * @param MasterFactory $factory
     */
    abstract protected function registerFactoriesIfMasterFactoryWasNotInjected(MasterFactory $factory);

    /**
     * @param HttpRouterChain $router
     */
    abstract protected function registerRouters(HttpRouterChain $router);

    /**
     * @return null
     */
    private function buildFactoryIfItWasNotInjected()
    {
        if (null !== $this->masterFactory) {
            return null;
        }

        $this->masterFactory = $this->createMasterFactoryIfNotInjected();

        if (!($this->masterFactory instanceof MasterFactory)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Factory is not of type MasterFactory but "%s"',
                    $this->getExceptionMessageClassNameRepresentation($this->masterFactory)
                )
            );
        }

        $this->registerFactoriesIfMasterFactoryWasNotInjected($this->masterFactory);
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

        return (string)$value;
    }

    /**
     * @return MasterFactory
     */
    public function getMasterFactory()
    {
        return $this->masterFactory;
    }

    /**
     * @return Environment
     */
    final protected function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return HttpRequest
     */
    final protected function getRequest()
    {
        return $this->request;
    }
}
