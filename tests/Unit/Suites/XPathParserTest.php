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

		$expectation = [['nodeName' => 'grandChild', 'attributes' => [], 'value' => 'foo']];

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

		$expectation = [['nodeName' => 'child', 'attributes' => [], 'value' => 'foo']];

		$this->assertSame($expectation, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnRequestedDomNodeRelativeToACurrentNode()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><foo><bar>baz</bar></foo></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('//foo/bar');

		$expectation = [['nodeName' => 'bar', 'attributes' => [], 'value' => 'baz']];

		$this->assertSame($expectation, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnCurrentDomNode()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><foo>bar</foo></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('.');

		$expectation = [[
			'nodeName'      => 'root',
			'attributes'    => [],
			'value'         => [[
				'nodeName'      => 'foo',
				'attributes'    => [],
				'value'         => 'bar'
			]]
		]];

		$this->assertSame($expectation, $result);
	}


	/**
	 * @test
	 */
	public function itShouldReturnParentDomNode()
	{
		$xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><foo>bar</foo></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('//foo/..');

		$expectation = [[
			'nodeName'      => 'root',
			'attributes'    => [],
			'value'         => [[
				'nodeName'      => 'foo',
				'attributes'    => [],
				'value'         => 'bar'
			]]
		]];

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
			['nodeName' => 'child', 'attributes' => [], 'value' => 'foo'],
			['nodeName' => 'child', 'attributes' => [], 'value' => 'bar']
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
			'nodeName'          => 'child',
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

		$expectation = [['nodeName' => 'child', 'attributes' => [], 'value' => 'foo']];

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

		$expectation = [['nodeName' => 'child', 'attributes' => [], 'value' => 'foo']];

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
			'nodeName' => 'root', 'attributes' => [], 'value' => [
				['nodeName' => 'child', 'attributes' => [], 'value' => 'foo'],
				['nodeName' => 'child', 'attributes' => ['baz' => 'qux'], 'value' => 'bar']
			]
		]];

		$this->assertSame($expectation, $result);
	}

	/**
	 * @test
	 */
	public function itShouldRemoveCommentNodesFromDom()
	{
		$xml = '<root><!-- comment1 --><child>foo</child><!-- comment2 --><child baz="qux">bar</child></root>';
		$parser = new XPathParser($xml);
		$result = $parser->getXmlNodesArrayByXPath('/root');

		$expectation = [[
			'nodeName' => 'root', 'attributes' => [], 'value' => [
				['nodeName' => 'child', 'attributes' => [], 'value' => 'foo'],
				['nodeName' => 'child', 'attributes' => ['baz' => 'qux'], 'value' => 'bar']
			]
		]];

		$this->assertSame($expectation, $result);
	}
}
