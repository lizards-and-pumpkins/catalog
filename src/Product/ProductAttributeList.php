<?php

namespace Brera\PoC\Product;

class ProductAttributeList
{
	/**
	 * @var ProductAttribute[]
	 */
	private $attributes = [];

	public function add(ProductAttribute $attribute)
	{
		array_push($this->attributes, $attribute);
	}

	/**
	 * @param string $code
	 * @param array $environment
	 * @return string|null
	 */
	public function getAttribute($code, $environment = [])
	{
		if (empty($code)) {
			/* TODO: Maybe trow a logical error instead? */
			return null;
		}

		foreach ($this->attributes as $attribute) {
			if ($attribute->hasCode($code)) {

				/* TODO: Implement closest environment match */

				return $attribute->getValue();
			}
		}

		return null;
	}
}
