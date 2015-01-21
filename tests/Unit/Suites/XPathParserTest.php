<?php

namespace Brera;

/**
 * @covers \Brera\XPathParser
 */
class XPathParserTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldReturnRequestedDomNodeArrayFromXmlWithNoNamespace()
	{
		$xml = '<root><child><grandChild>foo</grandChild></child></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('child/grandChild');

		$expectation = [['name' => 'grandChild', 'attributes' => [], 'value' => 'foo']];

		$this->assertEquals($expectation, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnRequestedDomNodeArrayFromXmlWithNamespace()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('child');

		$expectation = [['name' => 'child', 'attributes' => [], 'value' => 'foo']];

		$this->assertSame($expectation, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnMultipleNodeArrays()
	{
		$xml = '<root><child>foo</child><child>bar</child></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('child');

		$expectation = [
			['name' => 'child', 'attributes' => [], 'value' => 'foo'],
			['name' => 'child', 'attributes' => [], 'value' => 'bar']
		];

		$this->assertSame($expectation, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnArrayOfNodeWithAttributes()
	{
		$xml = '<root><child bar="baz" qux="waldo">foo</child></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('child');

		$expectation = [[
			'name'          => 'child',
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
		$parser = new XPathParser($xml);
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
		(new XPathParser($xml));
	}

	/**
	 * @test
	 */
	public function itShouldReturnANodeAttribute()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child foo="bar" /></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('child/@foo');

		$this->assertSame('bar', $result[0]['value']);
	}

	/**
	 * @test
	 */
	public function itShouldReturnANodeByAbsolutePath()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('/root/child');

		$expectation = [['name' => 'child', 'attributes' => [], 'value' => 'foo']];

		$this->assertSame($expectation, $result);
	}

	/**
	 * @test
	 */
	public function itShouldTolerateWildcardsInAPath()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('/*/child');

		$expectation = [['name' => 'child', 'attributes' => [], 'value' => 'foo']];

		$this->assertSame($expectation, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnArrayWithNestedXmlNodeRepresentation()
	{
		$xml = '<root><child>foo</child><child baz="qux">bar</child></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('/root');

		$expectation = [[
			'name' => 'root', 'attributes' => [], 'value' => [
				['name' => 'child', 'attributes' => [], 'value' => 'foo'],
				['name' => 'child', 'attributes' => ['baz' => 'qux'], 'value' => 'bar']
			]
		]];

		$this->assertSame($expectation, $result);
	}
}
