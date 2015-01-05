<?php

namespace Brera\Api;

use Brera\Http\HttpRequest;
use Brera\Http\HttpRouter;

class ApiRouter implements HttpRouter
{
	const API_URL_PREFIX = 'api';

	/**
	 * @var ApiRequestHandlerChain
	 */
	private $requestHandlerChain;

	public function __construct(ApiRequestHandlerChain $requestHandlerChain)
	{
		$this->requestHandlerChain = $requestHandlerChain;
	}

	/**
	 * @param HttpRequest $request
	 * @return ApiRequestHandler|null
	 */
	public function route(HttpRequest $request)
	{
		$urlPath = trim($request->getUrl()->getPath(), DIRECTORY_SEPARATOR);

		$urlToken = explode(DIRECTORY_SEPARATOR, $urlPath);

		if ($this::API_URL_PREFIX !== array_shift($urlToken)) {
			return null;
		}

		$requestHandlerCode = array_shift($urlToken);
		$apiRequestHandler = $this->requestHandlerChain->getApiRequestHandler($requestHandlerCode);

		$method = array_shift($urlToken);
		if (!method_exists($apiRequestHandler, $method)) {
			return null;
		}

		$apiRequestHandler->setMethod($method);

		return $apiRequestHandler;
	}
} 
