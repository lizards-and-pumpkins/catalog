<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;

/**
 * @covers \Brera\SampleUrlPathKeyGenerator
 */
class SampleUrlPathKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleUrlPathKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keyGenerator;

    public function setUp()
    {
        $this->keyGenerator = new SampleUrlPathKeyGenerator();
    }

    /**
     * @dataProvider urlKeyDataProvider
     * @param string $path
     * @param string $expected
     */
    public function testUrlKeySnippetIsCreatedForGivenPath($path, $expected)
    {
        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubUrl->method('getPathRelativeToWebFront')
            ->willReturn($path);
        
        $mockContext = $this->getMock(Context::class);
        $mockContext->method('getId')
            ->willReturn('v1');
        $result = $this->keyGenerator->getUrlKeyForUrlInContext($stubUrl, $mockContext);
        
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
