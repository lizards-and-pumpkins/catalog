<?php

namespace Brera\PoC;

/**
 * @covers Brera\PoC\PoCDomParser
 */
class PoCDomParserTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldReturnRequestedDomNodeWithoutNamespaceSet()
	{
		$xml = '<root><child><grandChild>foo</grandChild></child></root>';
		$parser = new PoCDomParser($xml);
		$result = $parser->getXPathNode('child/grandChild');

		$this->assertInstanceOf(\DOMNodeList::class, $result);
		$this->assertEquals('foo', $result->item(0)->nodeValue);
	}

	/**
	 * @test
	 */
	public function itShouldReturnRequestedDomNodeWithNamespaceSet()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
		$parser = new PoCDomParser($xml);
		$result = $parser->getXPathNode('child');

		$this->assertInstanceOf(\DOMNodeList::class, $result);
		$this->assertEquals('foo', $result->item(0)->nodeValue);
	}

	/**
	 * @test
	 */
	public function itShouldReturnANodeList()
	{
		$xml = '<root><child>foo</child><child>bar</child></root>';
		$parser = new PoCDomParser($xml);
		$result = $parser->getXPathNode('child');

		$this->assertInstanceOf(\DOMNodeList::class, $result);
		$this->assertEquals(2, $result->length);
	}

	/**
	 * @test
	 */
	public function itShouldReturnFirstElementOfANode()
	{
		$xml = '<root><child>foo</child><child>bar</child></root>';
		$parser = new PoCDomParser($xml);
		$result = $parser->getXPathFirstElementOfANode('child');

		$this->assertInstanceOf(\DOMElement::class, $result);
		$this->assertEquals('foo', $result->nodeValue);
	}

	/**
	 * @test
	 */
	public function itShouldReturnNullIfNoFirstElementIsFound()
	{
		$xml = '<root></root>';
		$parser = new PoCDomParser($xml);
		$result = $parser->getXPathFirstElementOfANode('child');

		$this->assertNull($result);
	}

	/**
	 * @test
	 * @expectedException \OutOfBoundsException
	 */
	public function itShouldThrowAnErrorIfXmlIsNotValid()
	{
		$xml = '<root xmlns="blah"></root>';
		(new PoCDomParser($xml));
	}
}
