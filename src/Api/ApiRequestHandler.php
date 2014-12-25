<?php

namespace Brera\PoC\Api;

use Brera\PoC\Http\HttpRequestHandler;

abstract class ApiRequestHandler implements HttpRequestHandler
{
	/**
	 * @var string
	 */
	private $action;

	public function process()
	{
		return call_user_func(array($this, $this->action));
	}

	/**
	 * @param string $action
	 */
	public function setMethod($action)
	{
		$this->action = $action;
	}
}
