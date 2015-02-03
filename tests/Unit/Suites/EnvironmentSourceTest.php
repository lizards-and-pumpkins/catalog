<?php


namespace Brera;

/**
 * @covers \Brera\EnvironmentSource
 */
class EnvironmentSourceTest extends \PHPUnit_Framework_TestCase
{
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
        $this->stubEnvironmentBuilder = $this->getMock(EnvironmentBuilder::class);
        $this->stubEnvironmentBuilder->expects($this->any())
            ->method('getEnvironments')
            ->willReturn([]);
        $environments = [VersionedEnvironment::KEY => [1]];
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
                    VersionedEnvironment::KEY => [1],
                    'foo' => ['a']
                ],
                ['foo'],
                [
                    ['version' => 1, 'foo' => 'a']
                ]
            ),
            array(
                [
                    VersionedEnvironment::KEY => [1],
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
                    VersionedEnvironment::KEY => [1, 2],
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
}
