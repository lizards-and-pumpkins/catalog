<?php

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Http\ContentDelivery\Exception\NonExistingSnippetException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\SnippetTransformation;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilderSnippets
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
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
     * @var DataPoolReader|MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var Context|MockObject
     */
    private $stubContext;

    /**
     * @var PageMetaInfoSnippetContent|MockObject
     */
    private $stubPageMetaInfo;

    /**
     * @var string
     */
    private $contextIdFixture = 'v12';

    /**
     * @var SnippetKeyGeneratorLocator|MockObject
     */
    private $stubSnippetKeyGeneratorLocator;

    /**
     * @param string $rootSnippetCode
     * @param string $rootSnippetContent
     * @param string[] $childSnippetMap
     * @param string[] $containerSnippets
     */
    private function setDataPoolFixture(
        $rootSnippetCode,
        $rootSnippetContent,
        array $childSnippetMap,
        array $containerSnippets = []
    ) {
        $allSnippetCodes = array_merge([$rootSnippetCode], array_keys($childSnippetMap));
        $allSnippetContent = array_merge([$rootSnippetContent], array_values($childSnippetMap));
        $this->setPageMetaInfoFixture($rootSnippetCode, $allSnippetCodes, $containerSnippets);
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $allSnippetCodes
     * @param string[] $containerSnippets
     */
    private function setPageMetaInfoFixture($rootSnippetCode, array $allSnippetCodes, array $containerSnippets = [])
    {
        $pageMetaInfo = [
            ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE  => $rootSnippetCode,
            ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => $allSnippetCodes,
            ProductDetailPageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS => $containerSnippets,
        ];

        $this->mockDataPoolReader->method('getSnippet')->with($this->urlPathKeyFixture)
            ->willReturn(json_encode($pageMetaInfo));

        $this->stubPageMetaInfo->method('getPageSnippetCodes')->willReturn($allSnippetCodes);
        $this->stubPageMetaInfo->method('getRootSnippetCode')->willReturn($rootSnippetCode);
        $this->stubPageMetaInfo->method('getContainerSnippets')->willReturn($containerSnippets);
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

    private function fakeSnippetKeyGeneratorLocator(MockObject $fakeKeyGeneratorLocator)
    {
        $fixedKeyGeneratorMockFactory = function ($snippetCode) {
            $keyGenerator = $this->getMock(SnippetKeyGenerator::class, [], [], '', false);
            $keyGenerator->method('getKeyForContext')->willReturn($snippetCode);
            return $keyGenerator;
        };
        $fakeKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')
            ->willReturnCallback($fixedKeyGeneratorMockFactory);
    }

    private function fakeSnippetKeyGeneratorLocatorForRootOnly(MockObject $fakeSnippetKeyGeneratorLocator)
    {
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

        $this->pageBuilder = new PageBuilder($this->mockDataPoolReader, $this->stubSnippetKeyGeneratorLocator);
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
            'head'           => '<title>My Website!</title>',
            'body'           => '<h1>My Website!</h1>{{snippet nesting-level1}}',
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

    public function testPlaceholderIsReplacedWithNestedPlaceholdersIgnoringMissingSnippets()
    {
        $rootSnippetContent = '<html><head>{{snippet head}}</head><body>{{snippet body}}</body></html>';
        $childSnippetMap = [
            'head'           => '<title>My Website!</title>',
            'body'           => '<h1>My Website!</h1>{{snippet nesting-level1}}',
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
            'body'           => '<h1>My Website!</h1>{{snippet nesting-level1}}',
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
        $this->expectException(NonExistingSnippetException::class);

        $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);
    }

    public function testPageSpecificAdditionalSnippetsAreMergedIntoList()
    {
        $rootSnippetContent = 'Stub Content - {{snippet child1}}';
        $childSnippetContent = 'Child Content 1 - {{snippet added-later}}';

        $childSnippetCodeToContentMap = ['child1' => $childSnippetContent];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetCodeToContentMap);

        $mergedSnippetCodeToKeyMap = ['added-later' => 'test-key'];
        $mergedSnippetKeyToContentMap = ['test-key' => 'Added Content'];

        $this->pageBuilder->addSnippetsToPage($mergedSnippetCodeToKeyMap, $mergedSnippetKeyToContentMap);

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);

        $this->assertEquals('Stub Content - Child Content 1 - Added Content', $page->getBody());
    }

    public function testPageSpecificAdditionalSnippetIsMergedIntoList()
    {
        $rootSnippetContent = 'Stub Content - {{snippet child1}}';
        $childSnippetContent = 'Child Content 1 - {{snippet added-later}}';

        $childSnippetCodeToContentMap = ['child1' => $childSnippetContent];

        $this->setDataPoolFixture($this->testRootSnippetCode, $rootSnippetContent, $childSnippetCodeToContentMap);

        $this->pageBuilder->addSnippetToPage('added-later', 'Added Content');

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);

        $this->assertEquals('Stub Content - Child Content 1 - Added Content', $page->getBody());
    }

    public function testChildSnippetsWithNoRegisteredKeyGeneratorAreIgnored()
    {
        /** @var SnippetKeyGeneratorLocator|MockObject $stubKeyGeneratorLocator */
        $stubKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $this->fakeSnippetKeyGeneratorLocatorForRootOnly($stubKeyGeneratorLocator);

        $this->pageBuilder = new PageBuilder($this->mockDataPoolReader, $stubKeyGeneratorLocator);

        $childSnippetCodes = ['child1'];
        $this->setPageMetaInfoFixture($this->testRootSnippetCode, $childSnippetCodes);
        
        $rootSnippetContent = "This is returned even the child snippet doesn't have a key generator ";
        $this->mockDataPoolReader->method('getSnippets')
            ->willReturn([$this->testRootSnippetCode => $rootSnippetContent . '{{snippet child1}}',]);
        
        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);

        $this->assertEquals($rootSnippetContent, $page->getBody());
    }

    public function testTestSnippetTransformationIsNotCalledIfThereIsNoMatchingSnippet()
    {
        /** @var callable|MockObject $mockTransformation */
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
        /** @var callable|MockObject $mockTransformation */
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
        /** @var callable|MockObject $mockTransformationOne */
        $mockTransformationOne = $this->getMock(SnippetTransformation::class);
        $mockTransformationOne->expects($this->once())->method('__invoke')->with('<h1>My Website!</h1>')
            ->willReturn('result one');
        $this->pageBuilder->registerSnippetTransformation('body', $mockTransformationOne);

        /** @var callable|MockObject $mockTransformationTwo */
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

    public function testItCombinesSnippetsInContainers()
    {
        $rootSnippetContent = 'Stub Content - {{snippet container1}}';
        $childSnippetCodeToContentMap = [
            'child1' => 'Child 1',
            'child2' => 'Child 2',
        ];
        $containerSnippets = ['container1' => ['child1', 'child2']];

        $this->setDataPoolFixture(
            $this->testRootSnippetCode,
            $rootSnippetContent,
            $childSnippetCodeToContentMap,
            $containerSnippets
        );

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);

        $this->assertEquals('Stub Content - Child 1Child 2', $page->getBody());
    }

    public function testItCombinesNestedContainers()
    {
        $rootSnippetContent = 'Stub Content - {{snippet container1}}';
        $childSnippetCodeToContentMap = [
            'child1' => 'Child 1',
            'child2' => 'Child 2',
            'child3' => 'Child 3',
        ];
        $containerSnippets = [
            'container1' => ['child1', 'container2'],
            'container2' => ['child2', 'container3'],
            'container3' => ['child3'],
        ];

        $this->setDataPoolFixture(
            $this->testRootSnippetCode,
            $rootSnippetContent,
            $childSnippetCodeToContentMap,
            $containerSnippets
        );

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);

        $this->assertEquals('Stub Content - Child 1Child 2Child 3', $page->getBody());
    }

    public function testItCombinesSnippetsAddedToThePageBuilder()
    {
        $rootSnippetContent = 'Stub Content - {{snippet container1}} : {{snippet container2}}';
        $childSnippetCodeToContentMap = [
            'child1' => 'Child 1',
            'child2' => 'Child 2',
            'child3' => 'Child 3',
        ];
        $containerSnippets = ['container1' => ['child1']];

        $this->setDataPoolFixture(
            $this->testRootSnippetCode,
            $rootSnippetContent,
            $childSnippetCodeToContentMap,
            $containerSnippets
        );
        
        $this->pageBuilder->addSnippetToContainer('container1', 'child2');
        $this->pageBuilder->addSnippetToContainer('container2', 'child3');
        $this->pageBuilder->addSnippetToContainer('container2', 'child4');
        $this->pageBuilder->addSnippetToPage('child4', 'Child 4');

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);

        $this->assertEquals('Stub Content - Child 1Child 2 : Child 3Child 4', $page->getBody());
    }

    public function testLoadsSnippetsAddedToContainerFromTheDataPool()
    {
        $rootSnippetContent = 'Stub Content - {{snippet container1}}';

        $containerSnippetsInPageMetaInfo = [];
        $this->setPageMetaInfoFixture(
            $this->testRootSnippetCode,
            [$this->testRootSnippetCode],
            $containerSnippetsInPageMetaInfo
        );
        $allSnippetCodes = [$this->testRootSnippetCode, 'child1'];
        $allSnippetContent = [$rootSnippetContent, 'Child 1 Content'];
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);

        $this->pageBuilder->addSnippetToContainer('container1', 'child1');
        
        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);

        $this->assertEquals('Stub Content - Child 1 Content', $page->getBody());
    }
}
