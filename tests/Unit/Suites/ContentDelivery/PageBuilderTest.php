<?php

namespace LizardsAndPumpkins\ContentDelivery;

use LizardsAndPumpkins\ContentDelivery\PageBuilder\Exception\NonExistingSnippetException;
use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\SnippetTransformation;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\PageBuilder
 * @covers \LizardsAndPumpkins\ContentDelivery\PageBuilder\PageBuilderSnippets
 * @uses   \LizardsAndPumpkins\DefaultHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\MissingSnippetCodeMessage
 */
class PageBuilderTest extends \PHPUnit_Framework_TestCase
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
     * @var PageBuilder
     */
    private $pageBuilder;

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
     * @var string
     */
    private $contextIdFixture = 'v12';

    /**
     * @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGeneratorLocator;

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
        $property = new \ReflectionProperty($this->pageBuilder, $field);
        $property->setAccessible(true);
        return $property->getValue($this->pageBuilder);
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
     */
    private function setPageMetaInfoFixture($rootSnippetCode, array $allSnippetCodes)
    {
        $pageMetaInfo = [
            ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => $rootSnippetCode,
            ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => $allSnippetCodes
        ];

        $this->mockDataPoolReader->method('getSnippet')->with($this->urlPathKeyFixture)
            ->willReturn(json_encode($pageMetaInfo));

        $this->stubPageMetaInfo->method('getPageSnippetCodes')->willReturn($allSnippetCodes);
        $this->stubPageMetaInfo->method('getRootSnippetCode')->willReturn($rootSnippetCode);

    }

    /**
     * @param string[] $allSnippetCodes
     * @param string[] $allSnippetContent
     */
    private function setPageContentSnippetFixture(array $allSnippetCodes, array $allSnippetContent)
    {
        $allSnippetKeys = $allSnippetCodes;
        $pageSnippetKeyMap = array_combine($allSnippetKeys, $allSnippetContent);
        $this->mockDataPoolReader->method('getSnippets')->willReturn($pageSnippetKeyMap);
    }

    private function fakeSnippetKeyGeneratorLocator(\PHPUnit_Framework_MockObject_MockObject $fakeKeyGeneratorLocator)
    {
        $fixedKeyGeneratorMockFactory = function ($snippetCode) {
            $keyGenerator = $this->getMock(SnippetKeyGenerator::class, [], [], '', false);
            $keyGenerator->method('getKeyForContext')->willReturn($snippetCode);
            return $keyGenerator;
        };
        $fakeKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')
            ->willReturnCallback($fixedKeyGeneratorMockFactory);
    }

    private function fakeSnippetKeyGeneratorLocatorForRootOnly(
        \PHPUnit_Framework_MockObject_MockObject $fakeSnippetKeyGeneratorLocator
    ) {
        $fakeSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturnCallback(
            function ($snippetCode) {
                if ($snippetCode === $this->testRootSnippetCode) {
                    $keyGenerator = $this->getMock(SnippetKeyGenerator::class, [], [], '', false);
                    $keyGenerator->method('getKeyForContext')->willReturn($snippetCode);
                    return $keyGenerator;
                }
                throw new \Exception(sprintf('No key generator set for snippet "%s"', $snippetCode));
            }
        );
    }

    protected function setUp()
    {
        $this->stubContext = $this->getMock(Context::class);
        $this->stubContext->method('getIdForParts')->willReturn($this->contextIdFixture);

        $this->stubPageMetaInfo = $this->getMock(PageMetaInfoSnippetContent::class);

        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);

        $this->stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $this->fakeSnippetKeyGeneratorLocator($this->stubSnippetKeyGeneratorLocator);

        $this->stubLogger = $this->getMock(Logger::class);

        $this->pageBuilder = new PageBuilder(
            $this->mockDataPoolReader,
            $this->stubSnippetKeyGeneratorLocator,
            $this->stubLogger
        );
    }

    public function testHttpResponseIsReturned()
    {
        $rootSnippetContent = 'Stub Content';
        $childSnippetMap = [];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);
        $result = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
        $this->assertInstanceOf(HttpResponse::class, $result);
    }

    public function testPlaceholderIsReplacedWithoutNestedPlaceholders()
    {
        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $headContent = '<title>My Website!</title>';
        $bodyContent = '<h1>My Website!</h1>';
        $childSnippetMap = ['head' => $headContent, 'body' => $bodyContent];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $expectedContent = '<html><head>' . $headContent . '</head><body>' . $bodyContent . '</body></html>';

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
        $this->assertEquals($expectedContent, $page->getBody());
    }

    public function testPlaceholderIsReplacedWithNestedPlaceholdersDeeperThanTwo()
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

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
        $this->assertEquals($expectedContent, $page->getBody());
    }

    public function testPlaceholderIsReplacedWithNestedPlaceholdersAndDoNotCareAboutMissingSnippets()
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

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
        $this->assertEquals($expectedContent, $page->getBody());
    }

    public function testPlaceholderIsReplacedRegardlessOfSnippetOrder()
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

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
        $this->assertEquals($expectedContent, $page->getBody());
    }

    public function testExceptionIsThrownIfTheRootSnippetContentIsNotFound()
    {
        $childSnippetCodes = ['child1'];
        $allSnippetCodes = [];
        $allSnippetContent = [];
        $this->setPageMetaInfoFixture($this->testRootSnippetCode, $childSnippetCodes);
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);
        $this->setExpectedException(NonExistingSnippetException::class);

        $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
    }

    public function testLogIsWrittenIfChildSnippetContentIsNotFound()
    {
        $childSnippetCodes = ['child1'];
        $allSnippetCodes = [$this->testRootSnippetCode];
        $allSnippetContent = ['Dummy Root Content'];
        $this->setPageMetaInfoFixture($this->testRootSnippetCode, $childSnippetCodes);
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);
        $this->stubLogger->expects($this->once())
            ->method('log');
        $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
    }

    /**
     * @dataProvider callOrderDataProvider
     * @param bool $testLoadBeforeAdd
     */
    public function testPageSpecificAdditionalSnippetsAreMergedIntoList($testLoadBeforeAdd)
    {
        $rootSnippetContent = 'Stub Content';
        $childSnippetCodeToContentMap = ['child1' => 'Child Content 1'];
        $snippetCodeToKeyMap = ['test-code' => 'test-key'];
        $snippetKeyToContentMap = ['test-key' => 'test-content'];
        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetCodeToContentMap);

        if ($testLoadBeforeAdd) {
            $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
            $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
        } else {
            $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
            $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
        }

        $this->assertSnippetKeyIsMappedToContent('child1', 'Child Content 1');
        $this->assertSnippetCodeIsMappedToSnippetKey('test-code', 'test-key');
        $this->assertSnippetKeyIsMappedToContent('test-key', 'test-content');
    }

    /**
     * @return array[]
     */
    public function callOrderDataProvider()
    {
        return [
            'load-then-add' => [true],
            'add-then-load' => [false],
        ];
    }

    public function testChildSnippetsAreGracefullyHandledWithNoKeyGenerator()
    {
        /** @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject $stubKeyGeneratorLocator */
        $stubKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $this->fakeSnippetKeyGeneratorLocatorForRootOnly($stubKeyGeneratorLocator);

        $this->pageBuilder = new PageBuilder(
            $this->mockDataPoolReader,
            $stubKeyGeneratorLocator,
            $this->stubLogger
        );

        $childSnippetCodes = ['child1'];
        $this->setPageMetaInfoFixture($this->testRootSnippetCode, $childSnippetCodes);
        $this->mockDataPoolReader->method('getSnippets')
            ->willReturn([$this->testRootSnippetCode => 'Dummy Root Content']);
        $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
        $this->assertTrue(
            true,
            'Dummy assertion. What I want is that the page is built with unregistered key generators ' .
            'without throwing an exception'
        );
    }

    public function testTestSnippetTransformationIsNotCalledIfThereIsNoMatchingSnippet()
    {
        /** @var callable|\PHPUnit_Framework_MockObject_MockObject $mockTransformation */
        $mockTransformation = $this->getMock(SnippetTransformation::class);
        $mockTransformation->expects($this->never())->method('__invoke');
        $this->pageBuilder->registerSnippetTransformation('non-existing-snippet-code', $mockTransformation);

        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $childSnippetMap = [
            'head' => '<title>My Website!</title>',
            'body' => '<h1>My Website!</h1>',
        ];
        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);
        
        $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
    }

    public function testTestSnippetTransformationIsCalledIfThereIsAMatchingSnippet()
    {
        /** @var callable|\PHPUnit_Framework_MockObject_MockObject $mockTransformation */
        $mockTransformation = $this->getMock(SnippetTransformation::class);
        $mockTransformation->expects($this->once())->method('__invoke')->with('<h1>My Website!</h1>')
            ->willReturn('Transformed Content');
        $this->pageBuilder->registerSnippetTransformation('body', $mockTransformation);

        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $childSnippetMap = [
            'head' => '<title>My Website!</title>',
            'body' => '<h1>My Website!</h1>',
        ];
        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
    }

    public function testMultipleTestSnippetTransformationsForOneSnippetCanBeRegistered()
    {
        /** @var callable|\PHPUnit_Framework_MockObject_MockObject $mockTransformationOne */
        $mockTransformationOne = $this->getMock(SnippetTransformation::class);
        $mockTransformationOne->expects($this->once())->method('__invoke')->with('<h1>My Website!</h1>')
            ->willReturn('result one');
        $this->pageBuilder->registerSnippetTransformation('body', $mockTransformationOne);

        /** @var callable|\PHPUnit_Framework_MockObject_MockObject $mockTransformationTwo */
        $mockTransformationTwo = $this->getMock(SnippetTransformation::class);
        $mockTransformationTwo->expects($this->once())->method('__invoke')->with('result one')
            ->willReturn('result two');
        $this->pageBuilder->registerSnippetTransformation('body', $mockTransformationTwo);

        $rootSnippetContent = '<body>{{snippet body}}</body>';
        $childSnippetMap = [
            'head' => '<title>My Website!</title>',
            'body' => '<h1>My Website!</h1>',
        ];
        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetMap);

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
        $this->assertEquals('<body>result two</body>', $page->getBody());
    }
}
