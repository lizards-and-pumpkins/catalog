<?php

namespace Brera\PoC\Product;

use Brera\PoC\Api\ApiRequestHandler;

class ProductApiRequestHandler extends ApiRequestHandler
{
	protected function import()
	{
		return json_encode('dummy response');
	}
}
