<?php


namespace Brera\Context;

/**
 * @covers \Brera\Context\ContextSource
 */
class ContextSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    private $testVersion = [1];
    
    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;
    
    /**
     * @var ContextSource
     */
    private $contextSource;

    public function setUp()
    {
        $this->stubContextBuilder = $this->getMockBuilder(ContextBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubContextBuilder->expects($this->any())
            ->method('getContexts')
            ->willReturn([]);
        $contexts = [VersionedContext::CODE => $this->testVersion];
        $this->contextSource = new ContextSource($contexts, $this->stubContextBuilder);
    }

    /**
     * @test
     */
    public function itShouldReturnAnArray()
    {
        $result = $this->contextSource->extractContexts([]);
        $this->assertInternalType('array', $result);
    }

    /**
     * @test
     * @param array $contextMatrix
     * @param array $requested
     * @param array $expected
     * @dataProvider cartesianProductTestProvider
     */
    public function itShouldReturnTheCartesianProductOf(array $contextMatrix, array $requested, array $expected)
    {
        $contextSource = new ContextSource($contextMatrix, $this->stubContextBuilder);
        $method = new \ReflectionMethod($contextSource, 'extractCartesianProductOfContextsAsArray');
        $method->setAccessible(true);
        $invoke = $method->invoke($contextSource, $requested);
        $this->assertEquals($expected, $invoke);
    }

    public function cartesianProductTestProvider()
    {
        return [
            array(['version' => [1]], [],[['version' => 1]]),
            array(
                [
                    VersionedContext::CODE => [1],
                    'foo' => ['a']
                ],
                ['foo'],
                [
                    ['version' => 1, 'foo' => 'a']
                ]
            ),
            array(
                [
                    VersionedContext::CODE => [1],
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
                    VersionedContext::CODE => [1, 2],
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
    public function itShouldReturnTheContextValuesForAGivenPart()
    {
        $result = $this->contextSource->getContextValuesForPart(VersionedContext::CODE);
        $this->assertSame($this->testVersion, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnAnEmptyArrayForANonExistentContextPart()
    {
        $this->assertSame([], $this->contextSource->getContextValuesForPart('non-existent'));
    }
}
