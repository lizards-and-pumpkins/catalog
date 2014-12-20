<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\ProductAttribute
 */
class ProductAttributeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \DOMElement
	 */
	private $domElement;

	protected function setUp()
	{
		$document = new \DOMDocument();
		$this->domElement = $document->createElement('foo');
	}

	/**
	 * @test
	 */
	public function itShouldReturnAttributeCode()
	{
		$this->domElement->setAttribute('code', 'name');
		$attribute = ProductAttribute::fromDomElement($this->domElement);

		$this->assertEquals('name', $attribute->getCode());
	}

	/**
	 * @test
	 */
	public function itShouldReturnAttributeValue()
	{
		$this->domElement->setAttribute('code', 'name');
		$this->domElement->nodeValue = 'bar';
		$attribute = ProductAttribute::fromDomElement($this->domElement);

		$this->assertEquals('bar', $attribute->getValue());
	}

	/**
	 * @test
	 */
	public function itShouldReturnAttributeEnvironment()
	{
		$this->domElement->setAttribute('code', 'name');
		$this->domElement->setAttribute('lang', 'cs_CZ');
		$this->domElement->setAttribute('website', 'bar');
		$attribute = ProductAttribute::fromDomElement($this->domElement);

		$environmentExpectation = [
			'lang'      => 'cs_CZ',
			'website'    => 'bar'
		];

		$this->assertSame($environmentExpectation, $attribute->getEnvironment());
	}

	/**
	 * @test
	 * @expectedException \Brera\PoC\InvalidAttributeCodeException
	 */
	public function itShouldThrowAnExceptionIfAttributeHasNoCode()
	{
		ProductAttribute::fromDomElement($this->domElement);
	}
}
