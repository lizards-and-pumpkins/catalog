<?php

namespace Brera\Product;

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
	 * @throws ProductAttributeNotFoundException
	 * @return ProductAttribute
	 */
	public function getAttribute($code, $environment = [])
	{
		if (empty($code)) {
			throw new ProductAttributeNotFoundException('Can not get an attribute with blank code.');
		}

		foreach ($this->attributes as $attribute) {
			if ($attribute->isCodeEqualsTo($code)) {

				/* TODO: Implement closest environment match */

				return $attribute;
			}
		}

		throw new ProductAttributeNotFoundException(sprintf('Can not find an attribute with "%s" code.', $code));
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
