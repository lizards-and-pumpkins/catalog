<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;

class ProductApiRequestHandler extends ApiRequestHandler
{
	protected function import()
	{
		return json_encode('dummy response');
	}
}
