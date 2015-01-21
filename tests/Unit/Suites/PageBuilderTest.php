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

    protected function setUp()
    {
        $url = HttpUrl::fromString('http://example.com/product.html');

        $environment = $this->getMock(Environment::class);
        $environment->expects($this->any())->method('getVersion')
            ->willReturn('1');

        $this->dataPoolReader = $this->getMockBuilder(DataPoolReader::class)
            ->disableOriginalConstructor()->getMock();

        $this->pageBuilder = new PageBuilder(
            $url, $environment, $this->dataPoolReader
        );
    }

    /**
     * @test
     */
    public function itShouldReturnAPage()
    {
        $this->mockDataPoolReader('', [], []);

        $this->assertInstanceOf(Page::class, $this->pageBuilder->buildPage());
    }

    /**
     * @test
     */
    public function itShouldGetFirstSnippet()
    {
        $pageContent = 'my page';
        $this->mockDataPoolReader($pageContent, [], []);

        $page = $this->pageBuilder->buildPage();
        $this->assertEquals($pageContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceHolderWithoutEnvironmentVariables()
    {
        $pageContent = '<html><head>{{snippet head_placeholder}}</head><body>{{snippet body_placeholder}}</body></html>';
        $this->mockDataPoolReader(
            $pageContent,
            ['head_placeholder', 'body_placeholder'],
            [
                'head_placeholder' => '<title>My Website!</title>',
                'body_placeholder' => '<h1>My Website!</h1>'
            ]
        );

        $rendererContent = '<html><head><title>My Website!</title></head><body><h1>My Website!</h1></body></html>';

        $page = $this->pageBuilder->buildPage();
        $this->assertEquals($rendererContent, $page->getBody());
    }

    /**
     * @param string $pageContent
     * @param array $snippetList
     * @param array $snippets
     */
    private function mockDataPoolReader($pageContent, $snippetList, $snippets)
    {
        $this->dataPoolReader->expects($this->any())->method('getSnippet')
            ->willReturn($pageContent);
        $this->dataPoolReader->expects($this->any())->method('getSnippetList')
            ->willReturn($snippetList);
        $this->dataPoolReader->expects($this->any())->method('getSnippets')
            ->willReturn($snippets);
    }

}
