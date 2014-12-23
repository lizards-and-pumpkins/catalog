<?php

namespace Brera\PoC\Product;

class ProductAttributeList
{
	/**
	 * @var ProductAttribute[]
	 */
	private $attributes = [];

	/**
	 * @param ProductAttribute $attribute
	 * @return void
	 */
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

	/**
	 * @param \DOMNodeList $nodeList
	 * @return ProductAttributeList
	 */
	public static function fromDomNodeList(\DOMNodeList $nodeList)
	{
		$attributeList = new self();

		foreach ($nodeList as $node) {
			$attribute = ProductAttribute::fromDomElement($node);
			$attributeList->add($attribute);
		}

		return $attributeList;
	}
}
