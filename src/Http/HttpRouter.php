<?php

namespace Brera\Http;

interface HttpRouter
{
	/**
	 * @param HttpRequest $request
	 * @return HttpRequestHandler
	 */
	public function route(HttpRequest $request);
} 
