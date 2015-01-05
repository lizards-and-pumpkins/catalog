<?php

namespace Brera\Api;

use Brera\Http\HttpRequestHandler;

class ApiRequestHandlerChain
{
	private $requestHandlers = [];

	/**
	 * @param string $code
	 * @param HttpRequestHandler $requestHandler
	 * @return void
	 */
	public function register($code, HttpRequestHandler $requestHandler)
	{
		$this->requestHandlers[$code] = $requestHandler;
	}

	/**
	 * @param string $code
	 * @return HttpRequestHandler|null
	 */
	public function getApiRequestHandler($code)
	{
		if (!array_key_exists($code, $this->requestHandlers)) {
			return null;
		}

		return $this->requestHandlers[$code];
	}
} 
