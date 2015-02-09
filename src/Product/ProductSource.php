<?php

namespace Brera\Product;

use Brera\Environment\Environment;
use Brera\ProjectionSourceData;

class ProductSource implements ProjectionSourceData
{
	/**
	 * @var ProductId
	 */
	private $id;

	/**
	 * @var ProductAttributeList
	 */
	private $attributes;

	/**
	 * @param ProductId $id
	 * @param ProductAttributeList $attributes
	 */
	public function __construct(ProductId $id, ProductAttributeList $attributes)
	{
		$this->id = $id;
		$this->attributes = $attributes;
	}

	/**
	 * @return ProductId
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $code
	 * @return ProductAttributeList|string
	 */
	public function getAttributeValue($code)
	{
		$attribute = $this->attributes->getAttribute($code);

		return $attribute->getValue();
	}

	/**
	 * @param Environment $environment
	 * @return Product
	 */
	public function getProductForEnvironment(Environment $environment)
	{
		$attributes = $this->attributes->getAttributesForEnvironment($environment);
		return new Product($this->getId(), $attributes);
	}
}
