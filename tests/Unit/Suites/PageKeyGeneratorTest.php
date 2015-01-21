<?php

namespace Brera;

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
        $this->pageKeyGenerator = new PageKeyGenerator();
    }

    /**
     * @test
     */
    public function itShouldReturnStrings()
    {
        /* @var $env VersionedEnvironment|\PHPUnit_Framework_MockObject_MockObject */
        $env = $this->getMockBuilder(VersionedEnvironment::class)->disableOriginalConstructor()->getMock();
        $env->expects($this->any())->method('getVersion')->willReturn('1');

        $url = HttpUrl::fromString('http://example.com/product.html');

        $this->assertInternalType(
            'string',
            $this->pageKeyGenerator->getKeyForPage($url, $env)
        );

        $this->assertInternalType(
            'string',
            $this->pageKeyGenerator->getKeyForSnippetList($url, $env)
        );
    }

    /**
     * @test
     */
    public function itShouldGenerateAKeyForSnippetFromAnEnvironmentAndUrl()
    {
        /* @var $env VersionedEnvironment|\PHPUnit_Framework_MockObject_MockObject */
        $env = $this->getMockBuilder(VersionedEnvironment::class)->disableOriginalConstructor()->getMock();
        $env->expects($this->any())->method('getVersion')->willReturn('1');

        $url = HttpUrl::fromString('http://example.com/product.html');

        $this->assertEquals(
            '_product_html_1',
            $this->pageKeyGenerator->getKeyForPage($url, $env)
        );
    }

    /**
     * @test
     */
    public function itShouldGenerateAKeyForSnippetListFromAnEnvironmentAndUrl()
    {
        /* @var $env VersionedEnvironment|\PHPUnit_Framework_MockObject_MockObject */
        $env = $this->getMockBuilder(VersionedEnvironment::class)->disableOriginalConstructor()->getMock();
        $env->expects($this->any())->method('getVersion')->willReturn('1');

        $url = HttpUrl::fromString('http://example.com/product.html');

        $this->assertEquals(
            '_product_html_1_l',
            $this->pageKeyGenerator->getKeyForSnippetList($url, $env)
        );
    }
}
