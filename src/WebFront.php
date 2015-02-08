<?php

namespace Brera;

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
	 * @param HttpRequest $request
	 * @param MasterFactory $factory
	 */
	function __construct(HttpRequest $request, MasterFactory $factory = null)
	{
		$this->request = $request;
		$this->masterFactory = $factory;
	}

	/**
	 * @param bool $isProductive
	 * @return HttpResponse
	 */
	public function run($isProductive = true)
	{
		$this->buildFactory();

		$router = new HttpRouterChain();
		$this->registerRouters($router);

		$requestHandler = $router->route($this->request);

		$content = $requestHandler->process();

		// TODO add response locator to differ between Json, html, ...

		// TODO put response creation into factory, response depends on http version!

		$response = new DefaultHttpResponse();
		$response->setBody($content);

		if ($isProductive) {
			$response->send();
		}

		return $response;
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
	 * @return null
	 */
	private function buildFactory()
	{
		if (null !== $this->masterFactory) {
			return null;
		}

		$this->masterFactory = $this->createMasterFactory();

		if (!($this->masterFactory instanceof MasterFactory)) {
			throw new \InvalidArgumentException(
				sprintf(
					'Factory is not of type MasterFactory but "%s"',
					$this->getExceptionMessageClassNameRepresentation($this->masterFactory)
				)
			);
		}

		$this->registerFactories($this->masterFactory);
	}

	/**
	 * @param $value
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
} 
