<?php

namespace LizardsAndPumpkins\Utils;

/**
 * @covers \LizardsAndPumpkins\Utils\XPathParser
 */
class XPathParserTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestedDomNodeArrayIsReturnedFromXmlWithNoNamespace()
    {
        $xml = '<root><child><grandChild>foo</grandChild></child></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('child/grandChild');

        $expectation = [['nodeName' => 'grandChild', 'attributes' => [], 'value' => 'foo']];

        $this->assertEquals($expectation, $result);
    }

    public function testRequestedDomNodeArrayIsReturnedFromXmlWithNamespace()
    {
        $xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('child');

        $expectation = [['nodeName' => 'child', 'attributes' => [], 'value' => 'foo']];

        $this->assertSame($expectation, $result);
    }

    public function testRequestedDomNodeRelativeToACurrentNodeIsReturned()
    {
        $xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><foo><bar>baz</bar></foo></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('//foo/bar');

        $expectation = [['nodeName' => 'bar', 'attributes' => [], 'value' => 'baz']];

        $this->assertSame($expectation, $result);
    }

    public function testCurrentDomNodeIsReturned()
    {
        $xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><foo>bar</foo></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('.');

        $expectation = [
            [
                'nodeName'   => 'root',
                'attributes' => [],
                'value'      => [
                    [
                        'nodeName'   => 'foo',
                        'attributes' => [],
                        'value'      => 'bar'
                    ]
                ]
            ]
        ];

        $this->assertSame($expectation, $result);
    }

    public function testParentDomNodeIsReturned()
    {
        $xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><foo>bar</foo></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('//foo/..');

        $expectation = [
            [
                'nodeName'   => 'root',
                'attributes' => [],
                'value'      => [
                    [
                        'nodeName'   => 'foo',
                        'attributes' => [],
                        'value'      => 'bar'
                    ]
                ]
            ]
        ];

        $this->assertSame($expectation, $result);
    }

    public function testMultipleNodeArraysAreReturned()
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

    public function testArrayOfNodeWithAttributesIsReturned()
    {
        $xml = '<root><child bar="baz" qux="waldo">foo</child></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('child');

        $expectation = [
            [
                'nodeName'   => 'child',
                'attributes' => ['bar' => 'baz', 'qux' => 'waldo'],
                'value'      => 'foo'
            ]
        ];

        $this->assertSame($expectation, $result);
    }

    public function testNodeXmlIsReturned()
    {
        $xml = '<root><child>foo</child><child>bar</child></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesRawXmlArrayByXPath('child');

        $expectation = ['<child>foo</child>', '<child>bar</child>'];

        $this->assertSame($expectation, $result);
    }

    public function testExceptionIsThrownIfXmlIsNotValid()
    {
        $this->expectException(\OutOfBoundsException::class);
        (new XPathParser('<root xmlns="blah"></root>'));
    }

    public function testNodeAttributeIsReturned()
    {
        $xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child foo="bar" /></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('child/@foo');

        $this->assertSame('bar', $result[0]['value']);
    }

    public function testNodeIsReturnedByAbsolutePath()
    {
        $xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('/root/child');

        $expectation = [['nodeName' => 'child', 'attributes' => [], 'value' => 'foo']];

        $this->assertSame($expectation, $result);
    }

    public function testWildcardsInPathAreTolerated()
    {
        $xml = '<root xmlns="http://www.w3.org/2001/XMLSchema-instance"><child>foo</child></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('/*/child');

        $expectation = [['nodeName' => 'child', 'attributes' => [], 'value' => 'foo']];

        $this->assertSame($expectation, $result);
    }

    public function testArrayWithNestedXmlNodeRepresentationIsReturned()
    {
        $xml = '<root><child>foo</child><child baz="qux">bar</child></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('/root');

        $expectation = [
            [
                'nodeName'   => 'root',
                'attributes' => [],
                'value'      => [
                    ['nodeName' => 'child', 'attributes' => [], 'value' => 'foo'],
                    ['nodeName' => 'child', 'attributes' => ['baz' => 'qux'], 'value' => 'bar']
                ]
            ]
        ];

        $this->assertSame($expectation, $result);
    }

    public function testCommentNodesAreRemovedFromDom()
    {
        $xml = '<root><!-- comment1 --><child>foo</child><!-- comment2 --><child baz="qux">bar</child></root>';
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('/root');

        $expectation = [
            [
                'nodeName'   => 'root',
                'attributes' => [],
                'value'      => [
                    ['nodeName' => 'child', 'attributes' => [], 'value' => 'foo'],
                    ['nodeName' => 'child', 'attributes' => ['baz' => 'qux'], 'value' => 'bar']
                ]
            ]
        ];

        $this->assertSame($expectation, $result);
    }

    public function testAllAttributesAreReturned()
    {
        $xml = <<<EOX
<root xmlns="http://www.w3.org/2001/XMLSchema-instance">
	<child>
		<grandChild foo="bar" />
	</child>
	<child baz="qux" />
</root>
EOX;
        $parser = new XPathParser($xml);
        $result = $parser->getXmlNodesArrayByXPath('//@*');

        $expectation = [
            ['nodeName' => 'foo', 'attributes' => ['foo' => 'bar'], 'value' => 'bar'],
            ['nodeName' => 'baz', 'attributes' => ['baz' => 'qux'], 'value' => 'qux']
        ];

        $this->assertSame($expectation, $result);
    }
}
