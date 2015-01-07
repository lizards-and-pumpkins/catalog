<?php

namespace Brera\Http;

interface HttpRequestHandler
{
	/**
	 * @return HttpResponse
	 */
	public function process();
} 
