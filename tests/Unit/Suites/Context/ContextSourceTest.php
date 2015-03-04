<?php


namespace Brera\Context;

/**
 * @covers \Brera\Context\ContextSource
 */
class ContextSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int[]
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
        $this->stubContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $this->stubContextBuilder->expects($this->any())
            ->method('getContexts')
            ->willReturn([]);
        $this->contextSource = $this->createContextSourceInstance($this->stubContextBuilder);
    }

    /**
     * @param ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $contextBuilder
     * @return ContextSource
     */
    private function createContextSourceInstance($contextBuilder)
    {
        $contexts = [VersionedContext::CODE => $this->testVersion];
        return new ContextSource($contexts, $contextBuilder);
    }

    /**
     * @test
     */
    public function itShouldReturnAnArray()
    {
        $result = $this->contextSource->extractContextsForParts([]);
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
        $result = $method->invoke($contextSource, $requested);
        $this->assertEquals($expected, $result);
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
            ),
            array(
                [
                    VersionedContext::CODE => [1, 2],
                    'foo' => ['a', 'b'],
                    'bar' => ['x', 'y'],
                ],
                ['foo', 'bar'],
                [
                    ['foo' => 'a', 'version' => 1, 'bar' => 'x'],
                    ['foo' => 'a', 'version' => 1, 'bar' => 'y'],
                    ['foo' => 'b', 'version' => 1, 'bar' => 'x'],
                    ['foo' => 'b', 'version' => 1, 'bar' => 'y'],
                    ['foo' => 'a', 'version' => 2, 'bar' => 'x'],
                    ['foo' => 'a', 'version' => 2, 'bar' => 'y'],
                    ['foo' => 'b', 'version' => 2, 'bar' => 'x'],
                    ['foo' => 'b', 'version' => 2, 'bar' => 'y'],
                ]
            ),
            array(
                [
                    VersionedContext::CODE => [1, 2],
                    'foo' => ['a', 'b', 'c'],
                ],
                ['foo'],
                [
                    ['foo' => 'a', 'version' => 1],
                    ['foo' => 'b', 'version' => 1],
                    ['foo' => 'c', 'version' => 1],
                    ['foo' => 'a', 'version' => 2],
                    ['foo' => 'b', 'version' => 2],
                    ['foo' => 'c', 'version' => 2],
                ]
            ),
            array(
                [
                    VersionedContext::CODE => [1, 2, 3],
                    'foo' => ['a', 'b', 'c'],
                ],
                ['foo'],
                [
                    ['foo' => 'a', 'version' => 1],
                    ['foo' => 'b', 'version' => 1],
                    ['foo' => 'c', 'version' => 1],
                    ['foo' => 'a', 'version' => 2],
                    ['foo' => 'b', 'version' => 2],
                    ['foo' => 'c', 'version' => 2],
                    ['foo' => 'a', 'version' => 3],
                    ['foo' => 'b', 'version' => 3],
                    ['foo' => 'c', 'version' => 3],
                ]
            ),
            array(
                [
                    VersionedContext::CODE => [1, 2],
                    'foo' => ['a'],
                    'bar' => ['x', 'y', 'z'],
                ],
                ['foo'],
                [
                    ['foo' => 'a', 'version' => 1],
                    ['foo' => 'a', 'version' => 2],
                ]
            ),
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

    /**
     * @test
     */
    public function itShouldReturnAllPossibleCombinations()
    {
        $stubContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $stubContextBuilder->expects($this->once())
            ->method('getContexts')
            ->with([[VersionedContext::CODE => reset($this->testVersion)]]);
        $contextSource = $this->createContextSourceInstance($stubContextBuilder);
        $contextSource->getAllAvailableContexts();
    }
}
