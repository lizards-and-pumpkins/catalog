<?php


namespace Brera;


use Brera\Http\HttpUrl;

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
        $env = new VersionedEnvironment(DataVersion::fromVersionString('1'));
        $url = HttpUrl::fromString('http://example.com/product.html');

        $this->assertInternalType(
            'string',
            $this->pageKeyGenerator->getKeyForSnippet($url, $env)
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
        $env = new VersionedEnvironment(DataVersion::fromVersionString('1'));
        $url = HttpUrl::fromString('http://example.com/product.html');

        $this->assertEquals(
            '_product_html_1',
            $this->pageKeyGenerator->getKeyForSnippet($url, $env)
        );
    }

    /**
     * @test
     */
    public function itShouldGenerateAKeyForSnippetListFromAnEnvironmentAndUrl()
    {
        $env = new VersionedEnvironment(DataVersion::fromVersionString('1'));
        $url = 'http://example.com/product.html';

        $this->assertEquals(
            '_product_html_1_l',
            $this->pageKeyGenerator->getKeyForSnippetList($url, $env)
        );
    }
}
