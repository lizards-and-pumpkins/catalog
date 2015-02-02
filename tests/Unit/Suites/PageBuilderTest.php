<?php

namespace Brera;

use Brera\Http\HttpUrl;
use Brera\KeyValue\DataPoolReader;

/**
 * @covers \Brera\PageBuilder
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Page
 * @uses   \Brera\PageKeyGenerator
 */
class PageBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPoolReader;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var HttpUrl
     */
    private $url;

    /**
     * @var PageKeyGenerator
     */
    private $pageKeyGenerator;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->url = HttpUrl::fromString('http://example.com/product.html');

        $this->environment = $this->getMock(Environment::class);
        $this->environment->expects($this->any())
            ->method('getVersion')
            ->willReturn('1');

        $this->dataPoolReader = $this->getMockBuilder(DataPoolReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageKeyGenerator = $this->getMockBuilder(PageKeyGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageKeyGenerator->expects($this->any())
            ->method('getKeyForSnippetList')
            ->willReturn('_product_html_1_l');
        $this->pageKeyGenerator->expects($this->any())
            ->method('getKeyForUrl')
            ->willReturn('_product_html_1');

        $this->pageBuilder = new PageBuilder($this->pageKeyGenerator, $this->dataPoolReader);
    }

    /**
     * @test
     */
    public function itShouldReturnAPage()
    {
        $this->mockDataPoolReader([], ['_product_html_1' => '']);

        $this->assertInstanceOf(Page::class, $this->pageBuilder->buildPage($this->url, $this->environment));
    }

    /**
     * @test
     */
    public function itShouldGetFirstSnippet()
    {
        $pageContent = 'my_page';
        $this->mockDataPoolReader([], ['_product_html_1' => $pageContent]);

        $page = $this->pageBuilder->buildPage($this->url, $this->environment);
        $this->assertEquals($pageContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithoutEnvironmentVariables()
    {
        $this->mockDataPoolReader(
            ['head_placeholder', 'body_placeholder'],
            [
                'head_placeholder' => '<title>My Website!</title>',
                'body_placeholder' => '<h1>My Website!</h1>',
                '_product_html_1'  => <<<EOH
<html><head>{{snippet head_placeholder}}</head><body>{{snippet body_placeholder}}</body></html>
EOH
            ]
        );

        $rendererContent = '<html><head><title>My Website!</title></head><body><h1>My Website!</h1></body></html>';

        $page = $this->pageBuilder->buildPage($this->url, $this->environment);
        $this->assertEquals($rendererContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithoutEnvironmentVariablesDeeperThanTwo()
    {
        $this->mockDataPoolReader(
            ['head_placeholder', 'body_placeholder', 'deep_1', 'deep_2', 'deep_3'],
            [
                'head_placeholder' => '<title>My Website!</title>',
                'body_placeholder' => '<h1>My Website!</h1>{{snippet deep_1}}',
                'deep_1'           => 'deep1{{snippet deep_2}}',
                'deep_2'           => 'deep2{{snippet deep_3}}',
                'deep_3'           => 'deep3',
                '_product_html_1'  => <<<EOH
<html><head>{{snippet head_placeholder}}</head><body>{{snippet body_placeholder}}</body></html>
EOH
            ]
        );

        $rendererContent = <<<EOH
<html><head><title>My Website!</title></head><body><h1>My Website!</h1>deep1deep2deep3</body></html>
EOH;

        $page = $this->pageBuilder->buildPage($this->url, $this->environment);
        $this->assertEquals($rendererContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithoutEnvironmentVariablesDeeperThanTwoAndDoNotCareAboutMissingSnippets()
    {
        $this->mockDataPoolReader(
            ['head_placeholder', 'body_placeholder', 'deep_1', 'deep_2', 'deep_3', 'deep_4'],
            [
                'head_placeholder' => '<title>My Website!</title>',
                'body_placeholder' => '<h1>My Website!</h1>{{snippet deep_1}}',
                'deep_1'           => 'deep1{{snippet deep_2}}',
                'deep_2'           => 'deep2{{snippet deep_3}}',
                'deep_3'           => 'deep3{{snippet deep_4}}',
                'deep_4'           => false,
                '_product_html_1'  => <<<EOH
<html><head>{{snippet head_placeholder}}</head><body>{{snippet body_placeholder}}</body></html>
EOH
            ]
        );

        $rendererContent = <<<EOH
<html><head><title>My Website!</title></head><body><h1>My Website!</h1>deep1deep2deep3</body></html>
EOH;


        $page = $this->pageBuilder->buildPage($this->url, $this->environment);
        $this->assertEquals($rendererContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderRegardlessOfSnippetOrder()
    {
        $this->mockDataPoolReader(
            ['body_placeholder', 'deep_1', 'deep_3', 'deep_2', 'deep_4'],
            [
                'body_placeholder' => '<h1>My Website!</h1>{{snippet deep_1}}',
                'deep_1'           => 'deep1{{snippet deep_2}}',
                'deep_3'           => 'deep3{{snippet deep_4}}',
                'deep_2'           => 'deep2{{snippet deep_3}}',
                'deep_4'           => false,
                '_product_html_1'  => '<html><body>{{snippet body_placeholder}}</body></html>'
            ]
        );

        $rendererContent = '<html><body><h1>My Website!</h1>deep1deep2deep3</body></html>';

        $page = $this->pageBuilder->buildPage($this->url, $this->environment);
        $this->assertEquals($rendererContent, $page->getBody());
    }

    /**
     * @param string[] $snippetList
     * @param string[] $snippets
     */
    private function mockDataPoolReader($snippetList, $snippets)
    {
        $this->dataPoolReader->expects($this->any())
            ->method('getChildSnippetKeys')
            ->willReturn($snippetList);
        $this->dataPoolReader->expects($this->any())
            ->method('getSnippets')
            ->willReturn($snippets);
    }
}
