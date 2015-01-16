<?php

namespace Brera\Product;

use Brera\Attribute;
use Brera\FirstCharOfAttributeCodeIsNotAlphabeticException;

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
	 * @throws FirstCharOfAttributeCodeIsNotAlphabeticException
	 * @return ProductAttribute
	 */
	public static function fromArray(array $node)
	{
		$code = $node['attributes']['code'];

		if (!strlen($code) || !ctype_alpha(substr($code, 0, 1))) {
			throw new FirstCharOfAttributeCodeIsNotAlphabeticException();
		}

		$environment = array_diff_key($node['attributes'], ['code' => $code]);

		return new self($code, $node['value'], $environment);
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
