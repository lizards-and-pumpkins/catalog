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
	 * @param Environment $environment
	 * @return $this
	 */
	public function getProductForEnvironment(Environment $environment)
	{
		$attributes = $this->attributes->getAttributesForEnvironment($environment);
		return new Product($this->getId(), $attributes);
	}
}
