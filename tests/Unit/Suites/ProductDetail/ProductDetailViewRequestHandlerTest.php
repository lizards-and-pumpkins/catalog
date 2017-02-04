<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\Routing\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductDetailViewRequestHandlerTest extends TestCase
{
    /**
     * @var ProductDetailViewRequestHandler
     */
    private $requestHandler;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var string
     */
    private $dummyMetaInfoKey = 'stub-meta-info-key';

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var string
     */
    private $dummyMetaInfoSnippetJson;

    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var string
     */
    private $testProductId = '123';

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var Translator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubTranslator;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $addSnippetsToPageSpy;

    private function createProductDetailPageMetaInfoContentJson() : string
    {
        return json_encode(ProductDetailPageMetaInfoSnippetContent::create(
            $this->testProductId,
            'root-snippet-code',
            ['child-snippet1'],
            []
        )->getInfo());
    }

    private function assertDynamicSnippetWasAddedToPageBuilder(string $snippetCode, string $snippetValue)
    {
        $numberOfTimesSnippetWasAddedToPageBuilder = array_sum(
            array_map(function ($invocation) use ($snippetCode, $snippetValue) {
                return intval([$snippetCode => $snippetCode] === $invocation->parameters[0] &&
                    [$snippetCode => $snippetValue] === $invocation->parameters[1]);
            }, $this->addSnippetsToPageSpy->getInvocations())
        );

        $this->assertEquals(1, $numberOfTimesSnippetWasAddedToPageBuilder, sprintf(
            'Failed to assert "%s" snippet with "%s" value was added to page builder.',
            $snippetCode,
            $snippetValue
        ));
    }

    protected function setUp()
    {
        $this->dummyMetaInfoSnippetJson = $this->createProductDetailPageMetaInfoContentJson();

        $this->mockDataPoolReader = $this->createMock(DataPoolReader::class);
        $this->stubContext = $this->createMock(Context::class);
        $this->mockPageBuilder = $this->createMock(PageBuilder::class);

        $this->addSnippetsToPageSpy = $this->any();
        $this->mockPageBuilder->expects($this->addSnippetsToPageSpy)->method('addSnippetsToPage');

        $this->stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);

        $this->stubTranslator = $this->createMock(Translator::class);

        /** @var TranslatorRegistry|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->createMock(TranslatorRegistry::class);
        $stubTranslatorRegistry->method('getTranslator')->willReturn($this->stubTranslator);

        $this->requestHandler = new ProductDetailViewRequestHandler(
            $this->stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $stubTranslatorRegistry,
            $this->stubSnippetKeyGenerator
        );

        $stubUrl = $this->createMock(HttpUrl::class);

        $this->stubRequest = $this->createMock(HttpRequest::class);
        $this->stubRequest->method('getUrl')->willReturn($stubUrl);
    }

    public function testRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testFalseIsReturnedIfPageMetaInfoContentSnippetCanNotBeLoaded()
    {
        $exception = new KeyNotFoundException();
        $this->mockDataPoolReader->method('getSnippet')->willThrowException($exception);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testTrueIsReturnedIfPageMetaInfoContentSnippetCanBeLoaded()
    {
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummyMetaInfoKey);
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->dummyMetaInfoKey, $this->dummyMetaInfoSnippetJson],
        ]);
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testExceptionIsThrownIfProcessWithoutMetaInfoContentIsCalled()
    {
        $this->expectException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubRequest);
    }

    public function testPageMetaInfoSnippetIsCreated()
    {
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummyMetaInfoKey);
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->dummyMetaInfoKey, $this->dummyMetaInfoSnippetJson],
        ]);

        $this->requestHandler->process($this->stubRequest);

        $this->assertAttributeInstanceOf(
            ProductDetailPageMetaInfoSnippetContent::class,
            'pageMetaInfo',
            $this->requestHandler
        );
    }

    public function testPageIsReturned()
    {
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummyMetaInfoKey);
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->dummyMetaInfoKey, $this->dummyMetaInfoSnippetJson],
        ]);
        $this->mockPageBuilder->method('buildPage')->with(
            $this->anything(),
            $this->anything(),
            $this->isType('array')
        )->willReturn($this->createMock(GenericHttpResponse::class));

        $this->assertInstanceOf(GenericHttpResponse::class, $this->requestHandler->process($this->stubRequest));
    }

    public function testItHandlesDifferentRequestsIndependently()
    {
        $urlKeyA = 'A.html';
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequestA */
        $stubRequestA = $this->createMock(HttpRequest::class);
        $stubRequestA->method('getPathWithoutWebsitePrefix')->willReturn($urlKeyA);

        $urlKeyB = 'B.html';
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequestB */
        $stubRequestB = $this->createMock(HttpRequest::class);
        $stubRequestB->method('getPathWithoutWebsitePrefix')->willReturn($urlKeyB);

        $requestAMetaInfoSnippetKey = 'A';
        $requestBMetaInfoSnippetKey = 'B';

        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturnMap([
            [$this->stubContext, [PageMetaInfoSnippetContent::URL_KEY => $urlKeyA], $requestAMetaInfoSnippetKey],
            [$this->stubContext, [PageMetaInfoSnippetContent::URL_KEY => $urlKeyB], $requestBMetaInfoSnippetKey],
        ]);

        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$requestAMetaInfoSnippetKey, $this->createProductDetailPageMetaInfoContentJson()],
            [$requestBMetaInfoSnippetKey, ''],
        ]);

        $this->assertTrue($this->requestHandler->canProcess($stubRequestA));
        $this->assertFalse($this->requestHandler->canProcess($stubRequestB));
    }

    public function testTranslationsAreAddedToPageBuilder()
    {
        $translations = ['foo' => 'bar'];

        $this->stubTranslator->method('jsonSerialize')->willReturn($translations);

        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummyMetaInfoKey);
        $this->mockDataPoolReader->method('getSnippet')
            ->willReturnMap([[$this->dummyMetaInfoKey, $this->dummyMetaInfoSnippetJson]]);
        $this->mockPageBuilder->method('buildPage')
            ->willReturn($this->createMock(GenericHttpResponse::class));

        $this->requestHandler->process($this->stubRequest);

        $snippetCode = 'translations';
        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, json_encode($translations));
    }

    public function testAddsRobotsMetaTagToHeadContainer()
    {
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummyMetaInfoKey);
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->dummyMetaInfoKey, $this->dummyMetaInfoSnippetJson],
        ]);

        $code = ProductDetailPageRobotsMetaTagSnippetRenderer::CODE;
        $this->mockPageBuilder->expects($this->once())->method('addSnippetToContainer')->with('head_container', $code);
        $this->mockPageBuilder->expects($this->once())->method('buildPage')
            ->with($this->anything(), $this->anything(), $this->arrayHasKey('robots'));

        $this->requestHandler->process($this->stubRequest);
    }
}
