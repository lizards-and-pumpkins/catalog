<?php


namespace Brera\Environment;

use Brera\DataVersion;

/**
 * @covers \Brera\Environment\EnvironmentSourceBuilder
 * @uses   \Brera\Environment\EnvironmentSource
 * @uses   \Brera\Environment\EnvironmentBuilder
 * @uses   \Brera\DataVersion
 * @uses   \Brera\XPathParser
 */
class EnvironmentSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EnvironmentSourceBuilder
     */
    private $environmentSourceBuilder;

    protected function setUp()
    {
        $stubVersion = $this->getMockBuilder(DataVersion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubVersion->expects($this->any())->method('__toString')->willReturn('1');

        $stubBuilder = $this->getMock(EnvironmentBuilder::class);
        $this->environmentSourceBuilder = new EnvironmentSourceBuilder($stubVersion, $stubBuilder);
    }

    /**
     * @test
     * @dataProvider invalidXmlTypesProvider
     * @expectedException \Brera\InvalidXmlTypeException
     */
    public function itShouldThrowIfNotAString($invalidXmlType)
    {
        $this->environmentSourceBuilder->createFromXml($invalidXmlType);
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
        $this->environmentSourceBuilder->createFromXml('');
    }

    /**
     * @test
     */
    public function itShouldReturnAnEnvironmentSourceInstance()
    {
        $result = $this->environmentSourceBuilder->createFromXml('<product></product>');
        $this->assertInstanceOf(EnvironmentSource::class, $result);
    }

    /**
     * @test
     */
    public function itShouldOnlyContainTheVersionIfThereAreNoAttributes()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(
            '<product><attributes><foo>true</foo></attributes></product>'
        );
        $this->assertEnvironmentPartCodesSame([VersionedEnvironment::CODE], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectASingleEnvironmentPart()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(
            '<product><attributes><foo baz="bar">true</foo></attributes></product>'
        );
        $this->assertEnvironmentPartCodesSame(['baz', VersionedEnvironment::CODE], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectTwoEnvironmentsFromTheSameAttribute()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(
            '<product><attributes><attribute foo="bar" baz="qux">true</attribute></attributes></product>'
        );
        $this->assertEnvironmentPartCodesSame(['foo', 'baz', VersionedEnvironment::CODE], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCombineTheSameEnvironmentsFromTwoAttributes()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(
            '<product><attributes><test1 foo="bar">true</test1><test2 foo="baz">true</test2></attributes></product>'
        );
        $this->assertEnvironmentPartCodesSame(['foo', VersionedEnvironment::CODE], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectDifferentEnvironmentsFromTwoAttributes()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(
            '<product><attributes><test1 foo="bar">true</test1><test2 baz="qux">true</test2></attributes></product>'
        );
        $this->assertEnvironmentPartCodesSame(['foo', 'baz', VersionedEnvironment::CODE], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectTheEnvironmentValues()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(
            '<product><attributes><test1 foo="bar">true</test1><test2 foo="baz">true</test2></attributes></product>'
        );
        $this->assertSame(['bar', 'baz'], $sourceEnv->getEnvironmentValuesForPart('foo'));
    }

    private function assertEnvironmentPartCodesSame($expected, EnvironmentSource $sourceEnv, $message = '')
    {
        $property = new \ReflectionProperty($sourceEnv, 'environmentMatrix');
        $property->setAccessible(true);
        $this->assertEquals($expected, array_keys($property->getValue($sourceEnv)), $message);
    }
}
