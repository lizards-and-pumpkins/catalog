<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\GenericHttpResponse;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var PageBuilder
     */
    private $mockPageBuilder;

    /**
     * @var HttpRequest
     */
    private $stubRequest;

    /**
     * @var Translator
     */
    private $stubTranslator;

    final protected function setUp(): void
    {
        $pageMeta = [
            ProductDetailPageMetaInfoSnippetContent::KEY_PRODUCT_ID => 'foo',
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => 'root-snippet-code',
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => ['child-snippet1'],
            PageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS => [],
            PageMetaInfoSnippetContent::KEY_PAGE_SPECIFIC_DATA => [],
        ];

        $stubContext = $this->createMock(Context::class);
        $this->mockPageBuilder = $this->createMock(PageBuilder::class);

        $this->stubTranslator = $this->createMock(Translator::class);

        /** @var TranslatorRegistry|MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->createMock(TranslatorRegistry::class);
        $stubTranslatorRegistry->method('getTranslator')->willReturn($this->stubTranslator);

        $this->requestHandler = new ProductDetailViewRequestHandler(
            $stubContext,
            $this->mockPageBuilder,
            $stubTranslatorRegistry,
            $pageMeta
        );

        $stubUrl = $this->createMock(HttpUrl::class);

        $this->stubRequest = $this->createMock(HttpRequest::class);
        $this->stubRequest->method('getUrl')->willReturn($stubUrl);
    }

    public function testRequestHandlerInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testCanProcessAnyRequest(): void
    {
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testPageIsReturned(): void
    {
        $this->mockPageBuilder->method('buildPage')->with(
            $this->anything(),
            $this->anything(),
            $this->isType('array')
        )->willReturn($this->createMock(GenericHttpResponse::class));

        $this->assertInstanceOf(GenericHttpResponse::class, $this->requestHandler->process($this->stubRequest));
    }

    public function testTranslationsAreAddedToPageBuilder(): void
    {
        $snippetCode = 'translations';
        $translations = ['foo' => 'bar'];

        $this->stubTranslator->method('jsonSerialize')->willReturn($translations);

        $this->mockPageBuilder->method('buildPage')
            ->willReturn($this->createMock(GenericHttpResponse::class));

        $this->mockPageBuilder->expects($this->at(0))->method('addSnippetsToPage')->with(
            [$snippetCode => $snippetCode],
            [$snippetCode => json_encode($translations)]
        );

        $this->requestHandler->process($this->stubRequest);
    }
}
