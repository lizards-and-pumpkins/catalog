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
    public function itShouldOnlyContainTheVersionIfThereAreNoAttributesWithEnvironmentValues()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(
            '<product><attributes><attribute code="test">true</attribute></attributes></product>'
        );
        $this->assertEnvironmentPartCodesSame([VersionedEnvironment::CODE], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectASingleEnvironmentPart()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(<<<EOX
<product><attributes>
    <attribute code="test" foo="bar">true</attribute>
</attributes></product>
EOX
        );
        $this->assertEnvironmentPartCodesSame(['foo', VersionedEnvironment::CODE], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectTwoEnvironmentsFromTheSameAttribute()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(<<<EOX
<product><attributes>
    <attribute code="test" foo="bar" baz="qux">true</attribute>
</attributes></product>
EOX
        );
        $this->assertEnvironmentPartCodesSame(['foo', 'baz', VersionedEnvironment::CODE], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCombineTheSameEnvironmentsFromTwoAttributes()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(<<<EOX
<product><attributes>
    <attribute code="test1" foo="bar">true</attribute>
    <attribute code="test2" foo="baz">true</attribute>
</attributes></product>
EOX
        );
        $this->assertEnvironmentPartCodesSame(['foo', VersionedEnvironment::CODE], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectDifferentEnvironmentsFromTwoAttributes()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(<<<EOX
<product><attributes>
    <attribute code="test1" foo="bar">true</attribute>
    <attribute code="test2" baz="qux">true</attribute>
</attributes></product>
EOX
        );
        $this->assertEnvironmentPartCodesSame(['foo', 'baz', VersionedEnvironment::CODE], $sourceEnv);
    }

    /**
     * @test
     */
    public function itShouldCollectTheEnvironmentValues()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(<<<EOX
<product><attributes>
    <attribute code="test1" foo="bar">true</attribute>
    <attribute code="test2" foo="baz">true</attribute>
</attributes></product>
EOX
        );
        $this->assertSame(['bar', 'baz'], $sourceEnv->getEnvironmentValuesForPart('foo'));
    }

    /**
     * @test
     */
    public function itShouldSkipAttributeNodesWithNoAttributes()
    {
        $sourceEnv = $this->environmentSourceBuilder->createFromXml(<<<EOX
<product><attributes>
    <attribute>true</attribute>
</attributes></product>
EOX
        );
        $this->assertEnvironmentPartCodesSame([VersionedEnvironment::CODE], $sourceEnv);
    }

    private function assertEnvironmentPartCodesSame($expected, EnvironmentSource $sourceEnv, $message = '')
    {
        $property = new \ReflectionProperty($sourceEnv, 'environmentMatrix');
        $property->setAccessible(true);
        $this->assertEquals($expected, array_keys($property->getValue($sourceEnv)), $message);
    }
}
