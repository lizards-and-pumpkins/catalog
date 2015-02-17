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
        $sourceEnv = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><foo>true</foo></attributes></product>'
        );
        $this->assertContextPartCodesSame([], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectASingleContextPart()
    {
        $sourceEnv = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><foo baz="bar">true</foo></attributes></product>'
        );
        $this->assertContextPartCodesSame(['baz'], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectTwoContextsFromTheSameAttribute()
    {
        $sourceEnv = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><attribute foo="bar" baz="qux">true</attribute></attributes></product>'
        );
        $this->assertContextPartCodesSame(['foo', 'baz'], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCombineTheSameContextsFromTwoAttributes()
    {
        $sourceEnv = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><test1 foo="bar">true</test1><test2 foo="baz">true</test2></attributes></product>'
        );
        $this->assertContextPartCodesSame(['foo'], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectDifferentContextsFromTwoAttributes()
    {
        $sourceEnv = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><test1 foo="bar">true</test1><test2 baz="qux">true</test2></attributes></product>'
        );
        $this->assertContextPartCodesSame(['foo', 'baz'], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectTheContextValues()
    {
        $sourceEnv = $this->contextSourceBuilder->createFromXml(
            '<product><attributes><test1 foo="bar">true</test1><test2 foo="baz">true</test2></attributes></product>'
        );
        $this->assertSame(['bar', 'baz'], $sourceEnv->getContextValuesForPart('foo'));
    }

    private function assertContextPartCodesSame($expected, ContextSource $sourceEnv, $message = '')
    {
        $property = new \ReflectionProperty($sourceEnv, 'contextMatrix');
        $property->setAccessible(true);
        $this->assertEquals($expected, array_keys($property->getValue($sourceEnv)), $message);
    }
}
