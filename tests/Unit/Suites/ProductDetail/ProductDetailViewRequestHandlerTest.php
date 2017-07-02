<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class ProductDetailViewRequestHandlerTest extends TestCase
{
    /**
     * @var ProductDetailViewRequestHandler
     */
    private $requestHandler;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

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
     * @var Translator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubTranslator;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $addSnippetsToPageSpy;

    private function createProductDetailPageMetaInfoContentJson(): string
    {
        return json_encode(ProductDetailPageMetaInfoSnippetContent::create(
            $this->testProductId,
            'root-snippet-code',
            ['child-snippet1'],
            $containers = [],
            $pageSpecificData = []
        )->toArray());
    }

    private function assertDynamicSnippetWasAddedToPageBuilder(string $snippetCode, string $snippetValue)
    {
        $numberOfTimesSnippetWasAddedToPageBuilder = array_sum(
            array_map(function ($invocation) use ($snippetCode, $snippetValue) {
                return (int) ([$snippetCode => $snippetCode] === $invocation->parameters[0] &&
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
        $metaJson = $this->createProductDetailPageMetaInfoContentJson();

        $this->stubContext = $this->createMock(Context::class);
        $this->mockPageBuilder = $this->createMock(PageBuilder::class);

        $this->addSnippetsToPageSpy = $this->any();
        $this->mockPageBuilder->expects($this->addSnippetsToPageSpy)->method('addSnippetsToPage');

        $this->stubTranslator = $this->createMock(Translator::class);

        /** @var TranslatorRegistry|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->createMock(TranslatorRegistry::class);
        $stubTranslatorRegistry->method('getTranslator')->willReturn($this->stubTranslator);

        $this->requestHandler = new ProductDetailViewRequestHandler(
            $this->stubContext,
            $this->mockPageBuilder,
            $stubTranslatorRegistry,
            $metaJson
        );

        $stubUrl = $this->createMock(HttpUrl::class);

        $this->stubRequest = $this->createMock(HttpRequest::class);
        $this->stubRequest->method('getUrl')->willReturn($stubUrl);
    }

    public function testRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testPageIsReturned()
    {
        $this->mockPageBuilder->method('buildPage')->with(
            $this->anything(),
            $this->anything(),
            $this->isType('array')
        )->willReturn($this->createMock(GenericHttpResponse::class));

        $this->assertInstanceOf(GenericHttpResponse::class, $this->requestHandler->process($this->stubRequest));
    }

    public function testTranslationsAreAddedToPageBuilder()
    {
        $translations = ['foo' => 'bar'];

        $this->stubTranslator->method('jsonSerialize')->willReturn($translations);

        $this->mockPageBuilder->method('buildPage')
            ->willReturn($this->createMock(GenericHttpResponse::class));

        $this->requestHandler->process($this->stubRequest);

        $snippetCode = 'translations';
        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, json_encode($translations));
    }
}
