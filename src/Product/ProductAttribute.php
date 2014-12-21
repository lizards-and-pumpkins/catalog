<?php

namespace Brera\PoC\Product;

use Brera\PoC\Attribute;
use Brera\PoC\InvalidAttributeCodeException;

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
	 * @param $code
	 * @param $value
	 * @param array $environment
	 */
	private function __construct($code, $value, $environment = array())
	{
		$this->code = $code;
		$this->environment = $environment;
		$this->value = $value;
	}

	public static function fromDomElement(\DOMElement $node)
	{
		$code = $node->getAttribute('code');

		if (empty($code)) {
			throw new InvalidAttributeCodeException();
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
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return array
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}
}
