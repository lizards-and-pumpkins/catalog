<?php

namespace Brera\Product;

use Brera\Attribute;

class ProductAttribute implements Attribute
{
	/**
	 * @var string
	 */
	private $code;

	/**
	 * @var array
	 */
	private $environment;

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @param string $code
	 * @param string $value
	 * @param array $environmentData
	 */
	private function __construct($code, $value, array $environmentData = [])
	{
		$this->code = $code;
		$this->environment = $environmentData;
		$this->value = $value;
	}

	/**
	 * @param array $node
	 * @return ProductAttribute
	 */
	public static function fromArray(array $node)
	{
		return new self($node['nodeName'], $node['value'], $node['attributes']);
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $codeExpectation
	 * @return bool
	 */
	public function isCodeEqualsTo($codeExpectation)
	{
		return $codeExpectation == $this->code;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}
}
