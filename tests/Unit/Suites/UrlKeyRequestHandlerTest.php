<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;

/**
 * @covers \Brera\UrlKeyRequestHandler
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Page
 */
class UrlKeyRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKeyRequestHandler
     */
    private $urlKeyRequestHandler;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContext;

    /**
     * @var HttpUrl
     */
    private $url;

    /**
     * @var UrlPathKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockUrlPathKeyGenerator;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->url = HttpUrl::fromString('http://example.com/product.html');

        $this->mockContext = $this->getMock(Context::class);
        $this->mockContext->expects($this->any())
            ->method('getVersion')
            ->willReturn('1');

        $this->mockDataPoolReader = $this->getMockBuilder(DataPoolReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->mockUrlPathKeyGenerator = $this->getMock(UrlPathKeyGenerator::class);
        $this->mockUrlPathKeyGenerator->expects($this->any())
            ->method('getUrlKeyForPathInContext')
            ->willReturn('dummy-url-key');

        $this->urlKeyRequestHandler = new UrlKeyRequestHandler(
            $this->url,
            $this->mockContext,
            $this->mockUrlPathKeyGenerator,
            $this->mockDataPoolReader
        );
    }

    /**
     * @param string $rootSnippetKey
     * @param string[] $snippetKeyList
     * @param string[] $snippetContent
     */
    private function stubDataPoolReaderMethods($rootSnippetKey, $snippetKeyList, $snippetContent)
    {
        // @todo: remove this method
        $this->mockDataPoolReader->expects($this->any())
            ->method('getSnippet')
            ->willReturn($rootSnippetKey);
        $this->mockDataPoolReader->expects($this->any())
            ->method('getChildSnippetKeys')
            ->willReturn($snippetKeyList);
        $this->mockDataPoolReader->expects($this->any())
            ->method('getSnippets')
            ->willReturn($snippetContent);
    }

    /**
     * @test
     */
    public function itShouldReturnAPage()
    {
        $this->markTestSkipped();
        $this->mockDataPoolReader->expects($this->once())
            ->method('getSnippet')
            ->willReturn(json_encode(['source_id' => 1, 'root_snippet_code' => '', 'page_snippet_codes' => ['']]));
        $this->assertInstanceOf(Page::class, $this->urlKeyRequestHandler->process());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithoutContextVariables()
    {
        $this->markTestSkipped();
        $this->stubDataPoolReaderMethods(
            'root_key',
            ['head_placeholder', 'body_placeholder'],
            [
                'head_placeholder' => '<title>My Website!</title>',
                'body_placeholder' => '<h1>My Website!</h1>',
                'root_key'  => <<<EOH
<html><head>{{snippet head_placeholder}}</head><body>{{snippet body_placeholder}}</body></html>
EOH
            ]
        );

        $rendererContent = '<html><head><title>My Website!</title></head><body><h1>My Website!</h1></body></html>';

        $page = $this->urlKeyRequestHandler->process();
        $this->assertEquals($rendererContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithoutContextVariablesDeeperThanTwo()
    {
        $this->markTestSkipped();
        $this->stubDataPoolReaderMethods(
            'root_key',
            ['head_placeholder', 'body_placeholder', 'deep_1', 'deep_2', 'deep_3'],
            [
                'head_placeholder' => '<title>My Website!</title>',
                'body_placeholder' => '<h1>My Website!</h1>{{snippet deep_1}}',
                'deep_1'           => 'deep1{{snippet deep_2}}',
                'deep_2'           => 'deep2{{snippet deep_3}}',
                'deep_3'           => 'deep3',
                'root_key'  => <<<EOH
<html><head>{{snippet head_placeholder}}</head><body>{{snippet body_placeholder}}</body></html>
EOH
            ]
        );

        $rendererContent = <<<EOH
<html><head><title>My Website!</title></head><body><h1>My Website!</h1>deep1deep2deep3</body></html>
EOH;

        $page = $this->urlKeyRequestHandler->process();
        $this->assertEquals($rendererContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithoutContextVariablesDeeperThanTwoAndDoNotCareAboutMissingSnippets()
    {
        $this->markTestSkipped();
        $this->stubDataPoolReaderMethods(
            '_product_html_1',
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


        $page = $this->urlKeyRequestHandler->process();
        $this->assertEquals($rendererContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderRegardlessOfSnippetOrder()
    {
        $this->markTestSkipped();
        $this->stubDataPoolReaderMethods(
            '_product_html_1',
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

        $page = $this->urlKeyRequestHandler->process();
        $this->assertEquals($rendererContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReturnFalseIfTheUrlKeySnippetIsNotKnown()
    {
        $this->mockDataPoolReader->expects($this->once())
            ->method('getSnippet')
            ->willThrowException(new KeyNotFoundException());
        $this->assertFalse($this->urlKeyRequestHandler->canProcess());
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfTheUrlKeySnippetIsKnown()
    {
        $this->mockDataPoolReader->expects($this->once())
            ->method('getSnippet')
            ->willReturn('test');
        $this->assertTrue($this->urlKeyRequestHandler->canProcess());
    }
}
