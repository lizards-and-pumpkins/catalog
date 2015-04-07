<?php

namespace Brera\Http;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;
use Brera\InvalidPageMetaSnippetException;
use Brera\Logger;
use Brera\Page;
use Brera\PageMetaInfoSnippetContent;
use Brera\Product\ProductDetailPageMetaInfoSnippetContent;

/**
 * @covers Brera\Http\AbstractHttpRequestHandler
 * @covers Brera\MissingSnippetCodeMessage
 * @covers Brera\Page
 */
final class AbstractRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $urlPathKeyFixture = 'dummy-url-key';

    /**
     * @var string
     */
    private $testRootSnippetCode = 'root-snippet';

    /**
     * @var string
     */
    private $contextIdFixture = 'v12';

    /**
     * @var AbstractHttpRequestHandler
     */
    private $requestHandler;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLogger;

    /**
     * @var PageMetaInfoSnippetContent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubPageMetaInfo;

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
     * @param array $allSnippetCodes
     */
    private function setPageMetaInfoFixture($rootSnippetCode, array $allSnippetCodes)
    {
        $pageMetaInfo = [
            ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => $rootSnippetCode,
            ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => $allSnippetCodes
        ];

        $this->mockDataPoolReader->expects($this->any())
            ->method('getSnippet')
            ->with($this->urlPathKeyFixture)
            ->willReturn(json_encode($pageMetaInfo));

        $this->stubPageMetaInfo->expects($this->any())
            ->method('getPageSnippetCodes')
            ->willReturn($allSnippetCodes);
        $this->stubPageMetaInfo->expects($this->any())->method('getRootSnippetCode')->willReturn($rootSnippetCode);

    }

    /**
     * @param string[] $allSnippetCodes
     * @param string[] $allSnippetContent
     */
    private function setPageContentSnippetFixture(array $allSnippetCodes, array $allSnippetContent)
    {
        $allSnippetKeys = $allSnippetCodes;
        $pageSnippetKeyMap = array_combine($allSnippetKeys, $allSnippetContent);
        $this->mockDataPoolReader->expects($this->any())
            ->method('getSnippets')
            ->willReturn($pageSnippetKeyMap);
    }

    /**
     * @param string $snippetCode
     * @param string $snippetKey
     */
    private function assertSnippetCodeIsMappedToSnippetKey($snippetCode, $snippetKey)
    {
        $field = 'snippetCodeToKeyMap';
        $this->assertArrayKeyIsMappedToValueOnRequestHandler($snippetCode, $snippetKey, $field);
    }

    /**
     * @param string $snippetKey
     * @param string $content
     */
    private function assertSnippetKeyIsMappedToContent($snippetKey, $content)
    {
        $field = 'snippetKeyToContentMap';
        $this->assertArrayKeyIsMappedToValueOnRequestHandler($snippetKey, $content, $field);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string $field
     */
    private function assertArrayKeyIsMappedToValueOnRequestHandler($key, $value, $field)
    {
        $map = $this->getPrivateFieldValue($field);
        $this->assertArrayHasKey($key, $map);
        $this->assertSame($value, $map[$key]);
    }

    /**
     * @param string $field
     * @return mixed
     */
    private function getPrivateFieldValue($field)
    {
        $abstractRequestHandler = get_parent_class($this->requestHandler);
        $property = new \ReflectionProperty($abstractRequestHandler, $field);
        $property->setAccessible(true);
        $value = $property->getValue($this->requestHandler);
        return $value;
    }

    protected function setUp()
    {
        $this->stubContext = $this->getMock(Context::class);
        $this->stubContext->expects($this->any())
            ->method('getIdForParts')
            ->willReturn($this->contextIdFixture);

        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->stubLogger = $this->getMock(Logger::class);

        $this->stubPageMetaInfo = $this->getMock(PageMetaInfoSnippetContent::class);

        $this->requestHandler = new HttpRequestHandlerSpy(
            $this->mockDataPoolReader,
            $this->stubLogger,
            $this->stubPageMetaInfo,
            $this->urlPathKeyFixture
        );
    }

    /**
     * @test
     */
    public function itShouldReturnAPage()
    {
        $rootSnippetContent = 'Stub Content';
        $childSnippetMap = [];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);
        $this->assertInstanceOf(Page::class, $this->requestHandler->process());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithoutNestedPlaceholders()
    {
        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $headContent = '<title>My Website!</title>';
        $bodyContent = '<h1>My Website!</h1>';
        $childSnippetMap = ['head' => $headContent, 'body' => $bodyContent];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $expectedContent = '<html><head>' . $headContent . '</head><body>' . $bodyContent . '</body></html>';

        $page = $this->requestHandler->process();
        $this->assertEquals($expectedContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithNestedPlaceholdersDeeperThanTwo()
    {
        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $childSnippetMap = [
            'head' => '<title>My Website!</title>',
            'body' => '<h1>My Website!</h1>{{snippet nesting-level1}}',
            'nesting-level1' => 'child1{{snippet nesting-level2}}',
            'nesting-level2' => 'child2{{snippet nesting-level3}}',
            'nesting-level3' => 'child3',
        ];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $expectedContent = <<<EOH
<html><head><title>My Website!</title></head><body><h1>My Website!</h1>child1child2child3</body></html>
EOH;

        $page = $this->requestHandler->process();
        $this->assertEquals($expectedContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderWithNestedPlaceholdersAndDoNotCareAboutMissingSnippets()
    {
        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $childSnippetMap = [
            'head' => '<title>My Website!</title>',
            'body' => '<h1>My Website!</h1>{{snippet nesting-level1}}',
            'nesting-level1' => 'child1{{snippet nesting-level2}}',
            'nesting-level2' => 'child2{{snippet nesting-level3}}',
            'nesting-level3' => 'child3{{snippet nesting-level4}}',
        ];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $expectedContent = <<<EOH
<html><head><title>My Website!</title></head><body><h1>My Website!</h1>child1child2child3</body></html>
EOH;

        $page = $this->requestHandler->process();
        $this->assertEquals($expectedContent, $page->getBody());
    }

    /**
     * @test
     */
    public function itShouldReplacePlaceholderRegardlessOfSnippetOrder()
    {
        $rootSnippetContent = '<html><body>{{snippet body}}</body></html>';
        $childSnippetMap = [
            'body' => '<h1>My Website!</h1>{{snippet nesting-level1}}',
            'nesting-level1' => 'child1{{snippet nesting-level2}}',
            'nesting-level3' => 'child3{{snippet nesting-level4}}',
            'nesting-level2' => 'child2{{snippet nesting-level3}}',
        ];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $expectedContent = '<html><body><h1>My Website!</h1>child1child2child3</body></html>';

        $page = $this->requestHandler->process();
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
        $this->assertFalse($this->requestHandler->canProcess());
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfTheUrlKeySnippetIsKnown()
    {
        $rootSnippetContent = 'Dummy Content';
        $childSnippetMap = [];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);
        $this->assertTrue($this->requestHandler->canProcess());
    }

    /**
     * @test
     * @expectedException \Brera\InvalidPageMetaSnippetException
     */
    public function itShouldThrowAnExceptionIfTheRootSnippetContentIsNotFound()
    {
        $childSnippetCodes = ['child1'];
        $allSnippetCodes = [];
        $allSnippetContent = [];
        $this->setPageMetaInfoFixture($this->testRootSnippetCode, $childSnippetCodes);
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);
        $this->requestHandler->process();
    }

    /**
     * @test
     */
    public function itShouldLogIfChildSnippetContentIsNotFound()
    {
        $childSnippetCodes = ['child1'];
        $allSnippetCodes = [$this->testRootSnippetCode];
        $allSnippetContent = ['Dummy Root Content'];
        $this->setPageMetaInfoFixture($this->testRootSnippetCode, $childSnippetCodes);
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);
        $this->stubLogger->expects($this->once())
            ->method('log');
        $this->requestHandler->process();
    }

    /**
     * @test
     */
    public function itShouldCallTheHookMethodMergingInTheResult()
    {
        /** @var HttpRequestHandlerSpy $requestHandlerSpy */
        $requestHandlerSpy = $this->requestHandler;

        $rootSnippetContent = 'Stub Content';
        $childSnippetMap = [];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);
        $requestHandlerSpy->process();
        $this->assertTrue($requestHandlerSpy->hookWasCalled);
    }

    /**
     * @test
     */
    public function itShouldMergePageSpecificAdditionalSnippetsIntoTheExistingList()
    {
        /** @var HttpRequestHandlerSpy $requestHandlerSpy */
        $requestHandlerSpy = $this->requestHandler;

        $rootSnippetContent = 'Stub Content';
        $childSnippetMap = [];
        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);
        $requestHandlerSpy->process();

        $snippetCodeToKeyMap = ['test-code' => 'test-key'];
        $snippetKeyToContentMap = ['test-key' => 'test-content'];
        $requestHandlerSpy->testAddSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);

        $this->assertSnippetCodeIsMappedToSnippetKey('test-code', 'test-key');
        $this->assertSnippetKeyIsMappedToContent('test-key', 'test-content');
    }

    /**
     * @test
     */
    public function itShouldCatchExceptionsWhileFetchingKeysForCodesAndUseAnEmptyStringForKey()
    {
        $rootSnippetContent = 'Stub Content';
        $childSnippetMap = [];
        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);
        $this->requestHandler->setThrowExceptionDuringSnippetKeyLookup(new \Exception('test'));
        try {
            $this->requestHandler->process();
        } catch (InvalidPageMetaSnippetException $e) {
            // The exception is thrown if the root snippet can't be mapped to a snippet key
            $this->assertSnippetCodeIsMappedToSnippetKey($this->testRootSnippetCode, '');
        }
    }
}
