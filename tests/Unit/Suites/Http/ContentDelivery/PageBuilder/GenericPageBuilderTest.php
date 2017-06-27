<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Http\ContentDelivery\Exception\NonExistingSnippetException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\SnippetTransformation;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Import\SnippetCode;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\GenericPageBuilder
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilderSnippets
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class GenericPageBuilderTest extends TestCase
{
    /**
     * @var string
     */
    private $urlPathKeyFixture = 'dummy-url-key';

    /**
     * @var SnippetCode
     */
    private $testRootSnippetCode;

    /**
     * @var GenericPageBuilder
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
     * @param SnippetCode $rootSnippetCode
     * @param string $rootSnippetContent
     * @param string[] $childSnippetMap
     * @param string[] $containerSnippets
     */
    private function setDataPoolFixture(
        SnippetCode $rootSnippetCode,
        string $rootSnippetContent,
        array $childSnippetMap,
        array $containerSnippets = []
    ) {
        $childSnippetCodes = $this->createSnippetCodesFromStrings(...array_keys($childSnippetMap));
        $allSnippetCodes = array_merge([$rootSnippetCode], $childSnippetCodes);
        $allSnippetContent = array_merge([$rootSnippetContent], array_values($childSnippetMap));
        $this->setPageMetaInfoFixture($rootSnippetCode, $allSnippetCodes, $containerSnippets);
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);
    }

    /**
     * @param string[] ...$snippetCodeStrings
     * @return SnippetCode[]
     */
    private function createSnippetCodesFromStrings(string ...$snippetCodeStrings): array
    {
        return array_map(function (string $snippetCodeString) {
            return new SnippetCode($snippetCodeString);
        }, $snippetCodeStrings);
    }

    /**
     * @param SnippetCode $rootSnippetCode
     * @param string[] $allSnippetCodes
     * @param string[] $containerSnippets
     */
    private function setPageMetaInfoFixture(
        SnippetCode $rootSnippetCode,
        array $allSnippetCodes,
        array $containerSnippets = []
    ) {
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
            $keyGenerator = $this->createMock(SnippetKeyGenerator::class);
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
                    $keyGenerator = $this->createMock(SnippetKeyGenerator::class);
                    $keyGenerator->method('getKeyForContext')->willReturn($snippetCode);
                    return $keyGenerator;
                }
                throw new \Exception(sprintf('No key generator set for snippet "%s"', $snippetCode));
            }
        );
    }

    final protected function setUp()
    {
        $this->testRootSnippetCode = new SnippetCode('root-snippet');

        $this->stubContext = $this->createMock(Context::class);
        $this->stubContext->method('getIdForParts')->willReturn($this->contextIdFixture);

        $this->stubPageMetaInfo = $this->createMock(PageMetaInfoSnippetContent::class);

        $this->mockDataPoolReader = $this->createMock(DataPoolReader::class);

        $this->stubSnippetKeyGeneratorLocator = $this->createMock(SnippetKeyGeneratorLocator::class);
        $this->fakeSnippetKeyGeneratorLocator($this->stubSnippetKeyGeneratorLocator);

        $this->pageBuilder = new GenericPageBuilder($this->mockDataPoolReader, $this->stubSnippetKeyGeneratorLocator);
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
        $childSnippetCodes = [new SnippetCode('child1')];
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

        $this->pageBuilder->addSnippetToPage(new SnippetCode('added-later'), 'Added Content');

        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);

        $this->assertEquals('Stub Content - Child Content 1 - Added Content', $page->getBody());
    }

    public function testChildSnippetsWithNoRegisteredKeyGeneratorAreIgnored()
    {
        /** @var SnippetKeyGeneratorLocator|MockObject $stubKeyGeneratorLocator */
        $stubKeyGeneratorLocator = $this->createMock(SnippetKeyGeneratorLocator::class);
        $this->fakeSnippetKeyGeneratorLocatorForRootOnly($stubKeyGeneratorLocator);

        $this->pageBuilder = new GenericPageBuilder($this->mockDataPoolReader, $stubKeyGeneratorLocator);

        $childSnippetCodes = [new SnippetCode('child1')];
        $this->setPageMetaInfoFixture($this->testRootSnippetCode, $childSnippetCodes);
        
        $rootSnippetContent = "This is returned even the child snippet doesn't have a key generator ";
        $this->mockDataPoolReader->method('getSnippets')
            ->willReturn([(string) $this->testRootSnippetCode => $rootSnippetContent . '{{snippet child1}}',]);
        
        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);

        $this->assertEquals($rootSnippetContent, $page->getBody());
    }

    public function testTestSnippetTransformationIsNotCalledIfThereIsNoMatchingSnippet()
    {
        $nonExistingSnippetCode = new SnippetCode('non-existing-snippet-code');

        /** @var callable|MockObject $mockTransformation */
        $mockTransformation = $this->createMock(SnippetTransformation::class);
        $mockTransformation->expects($this->never())->method('__invoke');
        $this->pageBuilder->registerSnippetTransformation($nonExistingSnippetCode, $mockTransformation);

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
        $mockTransformation = $this->createMock(SnippetTransformation::class);
        $mockTransformation->expects($this->once())->method('__invoke')->with('<h1>My Website!</h1>')
            ->willReturn('Transformed Content');
        $this->pageBuilder->registerSnippetTransformation(new SnippetCode('body'), $mockTransformation);

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
        $mockTransformationOne = $this->createMock(SnippetTransformation::class);
        $mockTransformationOne->expects($this->once())->method('__invoke')->with('<h1>My Website!</h1>')
            ->willReturn('result one');
        $this->pageBuilder->registerSnippetTransformation(new SnippetCode('body'), $mockTransformationOne);

        /** @var callable|MockObject $mockTransformationTwo */
        $mockTransformationTwo = $this->createMock(SnippetTransformation::class);
        $mockTransformationTwo->expects($this->once())->method('__invoke')->with('result one')
            ->willReturn('result two');
        $this->pageBuilder->registerSnippetTransformation(new SnippetCode('body'), $mockTransformationTwo);

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
            'container1' => [new SnippetCode('child1'), new SnippetCode('container2')],
            'container2' => [new SnippetCode('child2'), new SnippetCode('container3')],
            'container3' => [new SnippetCode('child3')],
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
        $containerSnippets = ['container1' => [new SnippetCode('child1')]];

        $this->setDataPoolFixture(
            $this->testRootSnippetCode,
            $rootSnippetContent,
            $childSnippetCodeToContentMap,
            $containerSnippets
        );
        
        $this->pageBuilder->addSnippetToContainer(new SnippetCode('container1'), new SnippetCode('child2'));
        $this->pageBuilder->addSnippetToContainer(new SnippetCode('container2'), new SnippetCode('child3'));
        $this->pageBuilder->addSnippetToContainer(new SnippetCode('container2'), new SnippetCode('child4'));
        $this->pageBuilder->addSnippetToPage(new SnippetCode('child4'), 'Child 4');

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
        $allSnippetCodes = [$this->testRootSnippetCode, new SnippetCode('child1')];
        $allSnippetContent = [$rootSnippetContent, 'Child 1 Content'];
        $this->setPageContentSnippetFixture($allSnippetCodes, $allSnippetContent);

        $this->pageBuilder->addSnippetToContainer(new SnippetCode('container1'), new SnippetCode('child1'));
        
        $page = $this->pageBuilder->buildPage($this->stubPageMetaInfo, $this->stubContext, []);

        $this->assertEquals('Stub Content - Child 1 Content', $page->getBody());
    }
}
