<?php


namespace Brera;

use Brera\Environment\Environment;

/**
 * @covers \Brera\PoCUrlPathKeyGenerator
 */
class PoCUrlPathKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PoCUrlPathKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keyGenerator;

    public function setUp()
    {
        $this->keyGenerator = new PoCUrlPathKeyGenerator();
    }

    /**
     * @test
     * @dataProvider urlKeyDataProvider
     */
    public function itShouldCreateUrlKeySnippetKeys($path, $expected)
    {
        $mockEnvironment = $this->getMock(Environment::class);
        $mockEnvironment->expects($this->any())
            ->method('getId')
            ->willReturn('v1');
        $result = $this->keyGenerator->getUrlKeyForPathInEnvironment($path, $mockEnvironment);
        $this->assertEquals($expected . '_v1', $result, "Unexpected url snippet key for path {$path}");
    }

    /**
     * @return array[]
     */
    public function urlKeyDataProvider()
    {
        return [
            ['foo', '_foo'],
            ['foo_:bar', '_foo_:bar'],
            ['/foo', '_foo'],
            ['foo123', '_foo123'],
            ['foo1/bar', '_foo1_bar'],
            ['/bar.html', '_bar_html'],
            ['/foo%', '_foo_'],
            ['///', '___'],
            ['$&"#', '_____'],
        ];
    }
}
