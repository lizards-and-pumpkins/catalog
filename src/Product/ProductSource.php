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
	 * @return string
	 */
	public function getAttributeValue($code)
	{
		$attribute = $this->attributes->getAttribute($code);

		return $attribute->getValue();
	}

	/**
	 * @param Environment $environment
	 * @return $this
	 */
	public function getProductForEnvironment(Environment $environment)
	{
		/** @todo: return Product containing only data matching the given environment */
		return $this;
	}
}
