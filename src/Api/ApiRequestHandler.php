<?php

namespace Brera\Api;

use Brera\Http\HttpRequestHandler;

abstract class ApiRequestHandler implements HttpRequestHandler
{
	/**
	 * @var string
	 */
	private $action;

	public function process()
	{
		return call_user_func([$this, $this->action]);
	}

	/**
	 * @param string $action
	 */
	public function setMethod($action)
	{
		$this->action = $action;
	}
}
