<?php

namespace Brera;

/**
 * @covers \Brera\DomDocumentXPathParser
 */
class DomDocumentXPathParserTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldReturnRequestedDomNodeArrayFromXmlWithNoNamespace()
	{
		$xml = '<root><child><grandChild>foo</grandChild></child></root>';
		$parser = new DomDocumentXPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('child/grandChild');

		$this->assertEquals('foo', $result[0]['value']);
	}

	/**
	 * @test
	 */
	public function itShouldReturnRequestedDomNodeArrayFromXmlWithNamespace()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
		$parser = new DomDocumentXPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('child');

		$this->assertSame('foo', $result[0]['value']);
	}

	/**
	 * @test
	 */
	public function itShouldReturnMultipleNodeArrays()
	{
		$xml = '<root><child>foo</child><child>bar</child></root>';
		$parser = new DomDocumentXPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('child');
		$expectation = [
			['attributes' => [], 'value' => 'foo'],
			['attributes' => [], 'value' => 'bar']
		];

		$this->assertSame($expectation, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnArrayOfNodeWithAttributes()
	{
		$xml = '<root><child bar="baz" qux="waldo">foo</child></root>';
		$parser = new DomDocumentXPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('child');
		$expectation = [[
			'attributes'    => ['bar' => 'baz', 'qux' => 'waldo'],
			'value'         => 'foo'
		]];

		$this->assertSame($expectation, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnNodeXml()
	{
		$xml = '<root><child>foo</child><child>bar</child></root>';
		$parser = new DomDocumentXPathParser($xml);
		$result = $parser->getXmlNodesRawXmlArrayByXPath('child');
		$expectation = ['<child>foo</child>', '<child>bar</child>'];

		$this->assertSame($expectation, $result);
	}

	/**
	 * @test
	 * @expectedException \OutOfBoundsException
	 */
	public function itShouldThrowAnErrorIfXmlIsNotValid()
	{
		$xml = '<root xmlns="blah"></root>';
		(new DomDocumentXPathParser($xml));
	}

	/**
	 * @test
	 */
	public function itShouldReturnANodeAttribute()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child foo="bar" /></root>';
		$parser = new DomDocumentXPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('child/@foo');

		$this->assertSame('bar', $result[0]['value']);
	}

	/**
	 * @test
	 */
	public function itShouldReturnANodeByAbsolutePath()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
		$parser = new DomDocumentXPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('/root/child');

		$this->assertSame('foo', $result[0]['value']);
	}

	/**
	 * @test
	 */
	public function itShouldTolerateWildcardsInAPath()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
		$parser = new DomDocumentXPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('/*/child');

		$this->assertSame('foo', $result[0]['value']);
	}
}
