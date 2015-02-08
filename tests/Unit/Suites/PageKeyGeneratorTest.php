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
        $stubEnv = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $stubEnv->expects($this->any())->method('getValue')->with('version')->willReturn('1');
        $this->pageKeyGenerator = new PageKeyGenerator($stubEnv);
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

        $this->assertInternalType(
            'string',
            $this->pageKeyGenerator->getKeyForSnippetList($url)
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

    /**
     * @test
     */
    public function itShouldGenerateAKeyForSnippetListFromAnEnvironmentAndUrl()
    {
        $url = HttpUrl::fromString('http://example.com/product.html');

        $this->assertEquals(
            '_product_html_1_l',
            $this->pageKeyGenerator->getKeyForSnippetList($url)
        );
    }
}
