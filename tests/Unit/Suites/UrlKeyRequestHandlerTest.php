<?php

namespace Brera;

use Brera\Environment\Environment;
use Brera\Http\HttpUrl;
use Brera\KeyValue\DataPoolReader;
use Brera\KeyValue\KeyNotFoundException;

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
     * @var Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEnvironment;

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

        $this->mockEnvironment = $this->getMock(Environment::class);
        $this->mockEnvironment->expects($this->any())
            ->method('getVersion')
            ->willReturn('1');

        $this->mockDataPoolReader = $this->getMockBuilder(DataPoolReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->mockUrlPathKeyGenerator = $this->getMock(UrlPathKeyGenerator::class);
        $this->mockUrlPathKeyGenerator->expects($this->any())
            ->method('getUrlKeyForPathInEnvironment')
            ->willReturn('dummy-url-key');

        $this->urlKeyRequestHandler = new UrlKeyRequestHandler(
            $this->url,
            $this->mockEnvironment,
            $this->mockUrlPathKeyGenerator,
            $this->mockDataPoolReader
        );
    }

    /**
     * @test
     */
    public function itShouldReturnAPage()
    {
        $this->stubDataPoolReaderMethods('root_key', [], ['root_key' => '']);
        $this->assertInstanceOf(Page::class, $this->urlKeyRequestHandler->process());
    }

    /**
     * @test
     */
    public function itShouldGetFirstSnippet()
    {
        $pageContent = 'my_page';
        $this->stubDataPoolReaderMethods('root_key', [], ['root_key' => $pageContent]);

        $page = $this->urlKeyRequestHandler->process();
        $this->assertEquals($pageContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithoutEnvironmentVariables()
    {
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
    public function itShouldReplacePlaceholderWithoutEnvironmentVariablesDeeperThanTwo()
    {
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
    public function itShouldReplacePlaceholderWithoutEnvironmentVariablesDeeperThanTwoAndDoNotCareAboutMissingSnippets()
    {
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
     * @param string $rootSnippetKey
     * @param string[] $snippetKeyList
     * @param string[] $snippetContent
     */
    private function stubDataPoolReaderMethods($rootSnippetKey, $snippetKeyList, $snippetContent)
    {
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
