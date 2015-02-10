<?php

namespace Brera;

use Brera\Environment\Environment;
use Brera\Http\HttpUrl;

/**
 * @covers \Brera\PageKeyGenerator
 * @uses   \Brera\Http\HttpUrl
 */
class PageKeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageKeyGenerator
     */
    private $pageKeyGenerator;

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Environment $environment */
        $stubEnvironment = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $stubEnvironment->expects($this->any())->method('getValue')->with('version')->willReturn('1');
        $this->pageKeyGenerator = new PageKeyGenerator($stubEnvironment);
    }

    /**
     * @test
     */
    public function itShouldReturnStrings()
    {
        $url = HttpUrl::fromString('http://example.com/product.html');

        $this->assertInternalType(
            'string',
            $this->pageKeyGenerator->getKeyForUrl($url)
        );
    }

    /**
     * @test
     */
    public function itShouldGenerateAKeyForSnippetFromAnEnvironmentAndUrl()
    {
        $url = HttpUrl::fromString('http://example.com/product.html');

        $this->assertEquals(
            '_product_html_1',
            $this->pageKeyGenerator->getKeyForUrl($url)
        );
    }
}
