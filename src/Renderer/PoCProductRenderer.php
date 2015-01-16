<?php

namespace Brera\Renderer;

use Brera\Product\Product;

class PoCProductRenderer implements ProductRenderer
{
	/**
	 * @param Product $product
	 * @return string
	 */
	public function render(Product $product)
	{
		return sprintf('<p>%s: %s</p>', $product->getId(), $product->getAttributeValue('name'));
	}
} 
