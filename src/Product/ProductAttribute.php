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
	 * @param \DOMElement $node
	 * @throws FirstCharOfAttributeCodeIsNotAlphabeticException
	 * @return ProductAttribute
	 */
	public static function fromDomElement(\DOMElement $node)
	{
		$code = $node->getAttribute('code');

		if (!strlen($code) || !ctype_alpha(substr($code, 0, 1))) {
			throw new FirstCharOfAttributeCodeIsNotAlphabeticException();
		}

		$value = $node->nodeValue;
		$environment = [];

		foreach ($node->attributes as $attributeCode => $attributeNode) {
			if ('code' === $attributeCode) {
				continue;
			}

			$environment[$attributeCode] = $attributeNode->value;
		}

		return new self($code, $value, $environment);
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
