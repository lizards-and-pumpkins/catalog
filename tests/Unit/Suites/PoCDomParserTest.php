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
	 * @expectedException \OutOfBoundsException
	 */
	public function itShouldThrowAnErrorIfXmlIsNotValid()
	{
		$xml = '<root xmlns="blah"></root>';
		(new PoCDomParser($xml));
	}

	/**
	 * @test
	 */
	public function itShouldReturnANodeAttribute()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child foo="bar" /></root>';
		$parser = new PoCDomParser($xml);
		$result = $parser->getXPathNode('child/@foo');

		$this->assertInstanceOf(\DOMNodeList::class, $result);
		$this->assertEquals('bar', $result->item(0)->nodeValue);
	}

	/**
	 * @test
	 */
	public function itShouldReturnANodeByAbsolutePath()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
		$parser = new PoCDomParser($xml);
		$result = $parser->getXPathNode('/root/child');

		$this->assertInstanceOf(\DOMNodeList::class, $result);
		$this->assertEquals('foo', $result->item(0)->nodeValue);
	}

	/**
	 * @test
	 */
	public function itShouldTolerateWildcardsInAPath()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
		$parser = new PoCDomParser($xml);
		$result = $parser->getXPathNode('/*/child');

		$this->assertInstanceOf(\DOMNodeList::class, $result);
		$this->assertEquals('foo', $result->item(0)->nodeValue);
	}

	/**
	 * @test
	 */
	public function itShouldReturnXmlRepresentationOfADomNode()
	{
		$xml = '<root><node/></root>';
		$parser = new PoCDomParser($xml);

		$node = $parser->getXPathNode('node');
		$result = $parser->getDomNodeXml($node->item(0));

		$this->assertEquals('<node/>', $result);
	}
}
