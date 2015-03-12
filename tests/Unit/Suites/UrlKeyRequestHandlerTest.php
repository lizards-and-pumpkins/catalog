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
 * @uses   \Brera\SnippetKeyGenerator
 * @uses   \Brera\PageMetaInfoSnippetContent
 * @uses   \Brera\MissingSnippetCodeMessage
 */
class UrlKeyRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    private $sourceIdFixture = 1;

    /**
     * @var string
     */
    private $contextIdFixture = 'v12';

    /**
     * @var string
     */
    private $urlPathKeyFixture = 'dummy-url-key';

    /**
     * @var UrlKeyRequestHandler
     */
    private $urlKeyRequestHandler;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var UrlPathKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockUrlPathKeyGenerator;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLogger;

    /**
     * @var HttpUrl
     */
    private $url;

    protected function setUp()
    {
        $this->url = HttpUrl::fromString('http://example.com/product.html');

        $this->stubContext = $this->getMock(Context::class);
        $this->stubContext->expects($this->any())->method('getId')->willReturn($this->contextIdFixture);

        $this->mockUrlPathKeyGenerator = $this->getMock(UrlPathKeyGenerator::class);
        $this->mockUrlPathKeyGenerator->expects($this->any())
            ->method('getUrlKeyForUrlInContext')
            ->willReturn($this->urlPathKeyFixture);

        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);

        $this->stubLogger = $this->getMock(Logger::class);

        $this->urlKeyRequestHandler = new UrlKeyRequestHandler(
            $this->url,
            $this->stubContext,
            $this->mockUrlPathKeyGenerator,
            $this->mockSnippetKeyGenerator,
            $this->mockDataPoolReader,
            $this->stubLogger
        );
    }

    /**
     * @test
     */
    public function itShouldReturnAPage()
    {
        $rootSnippetCode = 'root-snippet';
        $rootSnippetContent = 'Stub Content';
        $childSnippetMap = [];

        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn($this->getStubSnippetKey($rootSnippetCode));

        $this->setDataPoolFixture($rootSnippetCode, $rootSnippetContent, $childSnippetMap);
        $this->assertInstanceOf(Page::class, $this->urlKeyRequestHandler->process());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithoutNestedPlaceholders()
    {
        $rootSnippetCode = 'root-snippet';
        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $headContent = '<title>My Website!</title>';
        $bodyContent = '<h1>My Website!</h1>';
        $childSnippetMap = ['head' => $headContent, 'body' => $bodyContent];

        $this->setDataPoolFixture($rootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $expectedContent = '<html><head>' . $headContent . '</head><body>' . $bodyContent . '</body></html>';

        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn($this->getStubSnippetKey($rootSnippetCode));

        $page = $this->urlKeyRequestHandler->process();
        $this->assertEquals($expectedContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithNestedPlaceholdersDeeperThanTwo()
    {
        $rootSnippetCode = 'root-snippet';
        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $childSnippetMap = [
            'head' => '<title>My Website!</title>',
            'body' => '<h1>My Website!</h1>{{snippet nesting-level1}}',
            'nesting-level1' => 'child1{{snippet nesting-level2}}',
            'nesting-level2' => 'child2{{snippet nesting-level3}}',
            'nesting-level3' => 'child3',
        ];

        $this->setDataPoolFixture($rootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $expectedContent = <<<EOH
<html><head><title>My Website!</title></head><body><h1>My Website!</h1>child1child2child3</body></html>
EOH;

        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn($this->getStubSnippetKey($rootSnippetCode));

        $page = $this->urlKeyRequestHandler->process();
        $this->assertEquals($expectedContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithNestedPlaceholdersAndDoNotCareAboutMissingSnippets()
    {
        $rootSnippetCode = 'root-snippet';
        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $childSnippetMap = [
            'head' => '<title>My Website!</title>',
            'body' => '<h1>My Website!</h1>{{snippet nesting-level1}}',
            'nesting-level1' => 'child1{{snippet nesting-level2}}',
            'nesting-level2' => 'child2{{snippet nesting-level3}}',
            'nesting-level3' => 'child3{{snippet nesting-level4}}',
        ];

        $this->setDataPoolFixture($rootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $expectedContent = <<<EOH
<html><head><title>My Website!</title></head><body><h1>My Website!</h1>child1child2child3</body></html>
EOH;

        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn($this->getStubSnippetKey($rootSnippetCode));

        $page = $this->urlKeyRequestHandler->process();
        $this->assertEquals($expectedContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderRegardlessOfSnippetOrder()
    {
        $rootSnippetCode = 'root-snippet';
        $rootSnippetContent = '<html><body>{{snippet body}}</body></html>';
        $childSnippetMap = [
            'body' => '<h1>My Website!</h1>{{snippet nesting-level1}}',
            'nesting-level1' => 'child1{{snippet nesting-level2}}',
            'nesting-level3' => 'child3{{snippet nesting-level4}}',
            'nesting-level2' => 'child2{{snippet nesting-level3}}',
        ];

        $this->setDataPoolFixture($rootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $expectedContent = '<html><body><h1>My Website!</h1>child1child2child3</body></html>';

        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn($this->getStubSnippetKey($rootSnippetCode));

        $page = $this->urlKeyRequestHandler->process();
        $this->assertEquals($expectedContent, $page->getBody());
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
        $rootSnippetCode = 'root-snippet';
        $rootSnippetContent = 'Dummy Content';
        $childSnippetMap = [];

        $this->setDataPoolFixture($rootSnippetCode, $rootSnippetContent, $childSnippetMap);
        $this->assertTrue($this->urlKeyRequestHandler->canProcess());
    }

    /**
     * @test
     * @expectedException \Brera\InvalidPageMetaSnippetException
     */
    public function itShouldThrowAnExceptionIfTheRootSnippetContentIsNotFound()
    {
        $rootSnippetCode = 'root-snippet';
        $childSnippetCodes = ['child1'];
        $allSnippetCodes = [];
        $allSnippetContent = [];
        $this->setPageMetaInfoFixture($rootSnippetCode, $childSnippetCodes);
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);
        $this->urlKeyRequestHandler->process();
    }

    /**
     * @test
     */
    public function itShouldLogIfChildSnippetContentIsNotFound()
    {
        $rootSnippetCode = 'root-snippet';
        $childSnippetCodes = ['child1'];
        $allSnippetCodes = [$rootSnippetCode];
        $allSnippetContent = ['Dummy Root Content'];
        $this->setPageMetaInfoFixture($rootSnippetCode, $childSnippetCodes);
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);
        $this->stubLogger->expects($this->once())
            ->method('log');

        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturnMap([
                [$rootSnippetCode, $this->sourceIdFixture, $this->stubContext, $this->getStubSnippetKey($rootSnippetCode)],
                [$childSnippetCodes[0], $this->sourceIdFixture, $this->stubContext, $this->getStubSnippetKey($childSnippetCodes[0])]
            ]);

        $this->urlKeyRequestHandler->process();
    }

    /**
     * @param string $rootSnippetCode
     * @param string $rootSnippetContent
     * @param string[] $childSnippetMap
     */
    private function setDataPoolFixture($rootSnippetCode, $rootSnippetContent, array $childSnippetMap)
    {
        $allSnippetCodes = array_merge([$rootSnippetCode], array_keys($childSnippetMap));
        $allSnippetContent = array_merge([$rootSnippetContent], array_values($childSnippetMap));
        $this->setPageMetaInfoFixture($rootSnippetCode, $allSnippetCodes);
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $allSnippetCodes
     * @return mixed[]
     */
    private function buildStubPageMetaInfo($rootSnippetCode, array $allSnippetCodes)
    {
        $pageMetaInfo = [
            PageMetaInfoSnippetContent::KEY_SOURCE_ID => $this->sourceIdFixture,
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => $rootSnippetCode,
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => $allSnippetCodes
        ];
        return $pageMetaInfo;
    }

    /**
     * @param string $snippetCode
     * @return string
     */
    private function getStubSnippetKey($snippetCode)
    {
        return $snippetCode . '_' . $this->sourceIdFixture . '_' . $this->contextIdFixture;
    }

    /**
     * @param string $rootSnippetCode
     * @param array $allSnippetCodes
     */
    private function setPageMetaInfoFixture($rootSnippetCode, array $allSnippetCodes)
    {
        $pageMetaInfo = $this->buildStubPageMetaInfo($rootSnippetCode, $allSnippetCodes);

        $this->mockDataPoolReader->expects($this->any())
            ->method('getSnippet')
            ->with($this->urlPathKeyFixture)
            ->willReturn(json_encode($pageMetaInfo));
    }

    /**
     * @param string[] $allSnippetCodes
     * @param string[] $allSnippetContent
     */
    private function setPageContentSnippetFixture(array $allSnippetCodes, array $allSnippetContent)
    {
        $allSnippetKeys = array_map(function ($code) {
            return $this->getStubSnippetKey($code);
        }, $allSnippetCodes);
        $pageSnippetKeyMap = array_combine($allSnippetKeys, $allSnippetContent);
        $this->mockDataPoolReader->expects($this->any())
            ->method('getSnippets')
            ->willReturn($pageSnippetKeyMap);
    }
}
