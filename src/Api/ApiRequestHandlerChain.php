<?php

namespace Brera\PoC\Api;

class ApiRequestHandlerChain
{
	private $requestHandlers = [];

	/**
	 * @param string $code
	 * @param ApiRequestHandler $requestHandler
	 * @return void
	 */
	public function register($code, ApiRequestHandler $requestHandler)
	{
		$this->requestHandlers[$code] = $requestHandler;
	}

	/**
	 * @param string $code
	 * @return ApiRequestHandler|null
	 */
	public function getApiRequestHandler($code)
	{
		if (!array_key_exists($code, $this->requestHandlers)) {
			return null;
		}

		return $this->requestHandlers[$code];
	}
} 
