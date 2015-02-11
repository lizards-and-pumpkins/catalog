<?php


namespace Brera\Environment;

/**
 * @covers \Brera\Environment\EnvironmentSource
 */
class EnvironmentSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    private $testVersion = [1];
    
    /**
     * @var EnvironmentBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubEnvironmentBuilder;
    
    /**
     * @var EnvironmentSource
     */
    private $environmentSource;

    public function setUp()
    {
        $this->stubEnvironmentBuilder = $this->getMockBuilder(EnvironmentBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubEnvironmentBuilder->expects($this->any())
            ->method('getEnvironments')
            ->willReturn([]);
        $environments = [VersionedEnvironment::CODE => $this->testVersion];
        $this->environmentSource = new EnvironmentSource($environments, $this->stubEnvironmentBuilder);
    }

    /**
     * @test
     */
    public function itShouldReturnAnArray()
    {
        $result = $this->environmentSource->extractEnvironments([]);
        $this->assertInternalType('array', $result);
    }

    /**
     * @test
     * @param array $environmentMatrix
     * @param array $requested
     * @param array $expected
     * @dataProvider cartesianProductTestProvider
     */
    public function itShouldReturnTheCartesianProductOf(array $environmentMatrix, array $requested, array $expected)
    {
        $environmentSource = new EnvironmentSource($environmentMatrix, $this->stubEnvironmentBuilder);
        $method = new \ReflectionMethod($environmentSource, 'extractCartesianProductOfEnvironmentsAsArray');
        $method->setAccessible(true);
        $invoke = $method->invoke($environmentSource, $requested);
        $this->assertEquals($expected, $invoke);
    }

    public function cartesianProductTestProvider()
    {
        return [
            array(['version' => [1]], [],[['version' => 1]]),
            array(
                [
                    VersionedEnvironment::CODE => [1],
                    'foo' => ['a']
                ],
                ['foo'],
                [
                    ['version' => 1, 'foo' => 'a']
                ]
            ),
            array(
                [
                    VersionedEnvironment::CODE => [1],
                    'foo' => ['a', 'b']
                ],
                ['foo'],
                [
                    ['version' => 1, 'foo' => 'a'],
                    ['version' => 1, 'foo' => 'b']
                ]
            ),
            array(
                [
                    VersionedEnvironment::CODE => [1, 2],
                    'foo' => ['a', 'b']
                ],
                ['foo'],
                [
                    ['foo' => 'a', 'version' => 1],
                    ['foo' => 'b', 'version' => 1],
                    ['foo' => 'a', 'version' => 2],
                    ['foo' => 'b', 'version' => 2],
                ]
            )
        ];
    }

    /**
     * @test
     */
    public function itShouldReturnTheEnvironmentValuesForAGivenPart()
    {
        $result = $this->environmentSource->getEnvironmentValuesForPart(VersionedEnvironment::CODE);
        $this->assertSame($this->testVersion, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnAnEmptyArrayForANonExistentEnvironmentPart()
    {
        $this->assertSame([], $this->environmentSource->getEnvironmentValuesForPart('non-existent'));
    }
}
