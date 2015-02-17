<?php


namespace Brera\Context;

/**
 * @covers \Brera\Context\ContextSourceBuilder
 * @uses   \Brera\Context\ContextSource
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\DataVersion
 * @uses   \Brera\XPathParser
 */
class ContextSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextSourceBuilder
     */
    private $contextSourceBuilder;

    protected function setUp()
    {
        $stubBuilder = $this->getMockBuilder(ContextBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextSourceBuilder = new ContextSourceBuilder($stubBuilder);
    }

    /**
     * @test
     * @dataProvider invalidXmlTypesProvider
     * @expectedException \Brera\InvalidXmlTypeException
     */
    public function itShouldThrowIfNotAString($invalidXmlType)
    {
        $this->contextSourceBuilder->createFromXml($invalidXmlType);
    }

    public function invalidXmlTypesProvider()
    {
        return [
            [null],
            [array()],
            [new \stdClass()],
        ];
    }

    /**
     * @test
     * @expectedException \Brera\InputXmlIsEmptyStringException
     */
    public function itShouldThrowAnExceptionIfTheXmlIsEmptyString()
    {
        $this->contextSourceBuilder->createFromXml('');
    }

    /**
     * @test
     */
    public function itShouldReturnAnContextSourceInstance()
    {
        $result = $this->contextSourceBuilder->createFromXml('<product></product>');
        $this->assertInstanceOf(ContextSource::class, $result);
    }

    /**
     * @test
     */
    public function itShouldbeEmptyIfThereAreNoAttributes()
    {
        $sourceContext = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><foo>true</foo></attributes></product>'
        );
        $this->assertContextPartCodesSame([], $sourceContext);
    }

    /**
     * @test
     */
    public function itShouldCollectASingleContextPart()
    {
        $sourceContext = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><foo baz="bar">true</foo></attributes></product>'
        );
        $this->assertContextPartCodesSame(['baz'], $sourceContext);
    }

    /**
     * @test
     */
    public function itShouldCollectTwoContextsFromTheSameAttribute()
    {
        $sourceContext = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><attribute foo="bar" baz="qux">true</attribute></attributes></product>'
        );
        $this->assertContextPartCodesSame(['foo', 'baz'], $sourceContext);
    }

    /**
     * @test
     */
    public function itShouldCombineTheSameContextsFromTwoAttributes()
    {
        $sourceContext = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><test1 foo="bar">true</test1><test2 foo="baz">true</test2></attributes></product>'
        );
        $this->assertContextPartCodesSame(['foo'], $sourceContext);
    }

    /**
     * @test
     */
    public function itShouldCollectDifferentContextsFromTwoAttributes()
    {
        $sourceContext = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><test1 foo="bar">true</test1><test2 baz="qux">true</test2></attributes></product>'
        );
        $this->assertContextPartCodesSame(['foo', 'baz'], $sourceContext);
    }

    /**
     * @test
     */
    public function itShouldCollectTheContextValues()
    {
        $sourceContext = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><test1 foo="bar">true</test1><test2 foo="baz">true</test2></attributes></product>'
        );
        $this->assertSame(['bar', 'baz'], $sourceContext->getContextValuesForPart('foo'));
    }

    private function assertContextPartCodesSame($expected, ContextSource $sourceContext, $message = '')
    {
        $property = new \ReflectionProperty($sourceContext, 'contextMatrix');
        $property->setAccessible(true);
        $this->assertEquals($expected, array_keys($property->getValue($sourceContext)), $message);
    }
}
